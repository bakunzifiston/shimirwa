<?php

namespace App\Console\Commands;

use App\Models\Emballage;
use App\Models\Milling;
use App\Models\RawMaterialStock;
use App\Models\Roasting;
use App\Models\Sale;
use App\Models\Sorting;
use App\Models\StockAdjustment;
use App\Support\Inventory\PackagedUnits;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reconciles stock with a physical closing count.
 *
 * For every batch dated on or before the count date it works out the book
 * balance as of that date from the transaction history (received/produced
 * minus every draw dated on or before it), compares it with the counted
 * figure (0 when the batch isn't on the count sheet), and:
 *
 *   1. records the difference as a StockAdjustment dated on the count date,
 *      so the monthly report's "as of" balances match the count;
 *   2. sets the live balance column to counted minus whatever was drawn
 *      after the count date, so later entries (e.g. October) are kept.
 *
 * Re-running for the same date replaces that date's count adjustments.
 */
class ApplyClosingStockCount extends Command
{
    protected $signature = 'stock:apply-count
                            {date : Count date, e.g. 2026-09-30 (reads database/stock_counts/{date}.php)}
                            {--apply : Write the changes (without it, only shows what would change)}';

    protected $description = 'Reconcile stock balances with a physical closing stock count';

    private const EPSILON = 0.001;

    private const LIVE_COLUMNS = [
        StockAdjustment::TYPE_RAW      => ['raw_material_stocks', 'quantity_in'],
        StockAdjustment::TYPE_SORTING  => ['sortings', 'quantity_remaining'],
        StockAdjustment::TYPE_ROASTING => ['roastings', 'quantity_remaining'],
        StockAdjustment::TYPE_MILLING  => ['millings', 'output_flour'],
        StockAdjustment::TYPE_PACKAGED => ['emballages', 'item'],
    ];

    private const SECTION_TITLES = [
        StockAdjustment::TYPE_RAW      => 'Raw & packaging materials',
        StockAdjustment::TYPE_SORTING  => 'Sorting',
        StockAdjustment::TYPE_ROASTING => 'Roasting',
        StockAdjustment::TYPE_MILLING  => 'Milling (unpackaged flour)',
        StockAdjustment::TYPE_PACKAGED => 'Packaged flour',
    ];

    private string $date;

    /** @var array<string, array<int, array{0: string, 1: float}>> "type:id" => [[date, qty], ...] */
    private array $draws = [];

    /** @var array<string, array<int, array{0: string, 1: float}>> "type:id" => [[date, qty], ...] */
    private array $adjustments = [];

    /** @var array<int, float> emballage id => units originally packed */
    private array $packedUnits = [];

    /** @var array<int, string> */
    private array $errors = [];

    public function handle(): int
    {
        $this->date = Carbon::parse($this->argument('date'))->toDateString();

        $file = database_path("stock_counts/{$this->date}.php");
        if (! is_file($file)) {
            $this->error("No count file found at {$file}");

            return self::FAILURE;
        }

        if (! Schema::hasTable('stock_adjustments')) {
            $this->error('The stock_adjustments table is missing. Run `php artisan migrate` first.');

            return self::FAILURE;
        }

        $count = require $file;

        // Everything runs in one transaction: batches missing from the system
        // are created first so they're planned like any other batch, and a
        // dry run (or any error) rolls the whole thing back.
        DB::beginTransaction();

        try {
            $created = $this->createMissingBatches($count);

            $this->loadDraws();
            $this->loadAdjustments();

            $plan = [
                StockAdjustment::TYPE_RAW      => $this->planByBatch(StockAdjustment::TYPE_RAW, $this->rawCandidates(), $count['raw'] ?? []),
                StockAdjustment::TYPE_SORTING  => $this->planByBatch(StockAdjustment::TYPE_SORTING, $this->sortingCandidates(), $count['sorting'] ?? []),
                StockAdjustment::TYPE_ROASTING => $this->planByBatch(StockAdjustment::TYPE_ROASTING, $this->roastingCandidates(), $count['roasting'] ?? []),
                StockAdjustment::TYPE_MILLING  => $this->planByBatch(StockAdjustment::TYPE_MILLING, $this->millingCandidates(), $count['milling'] ?? []),
                StockAdjustment::TYPE_PACKAGED => $this->planPackaged($this->packagedCandidates(), $count['packaged'] ?? []),
            ];

            $this->report($plan, $created);

            if ($this->errors) {
                DB::rollBack();
                $this->newLine();
                foreach ($this->errors as $error) {
                    $this->error($error);
                }
                $this->error('Nothing was written. Fix the count file and run again.');

                return self::FAILURE;
            }

            if (! $this->option('apply')) {
                DB::rollBack();
                $this->newLine();
                $this->warn('Dry run: nothing was written. Run again with --apply to save these changes.');

                return self::SUCCESS;
            }

            [$adjusted, $updated] = $this->write($plan);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        $this->newLine();
        $this->info(sprintf('Created %d missing batch(es), recorded %d adjustment(s) dated %s and updated %d live balance(s).',
            count($created), $adjusted, $this->date, $updated));

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Batches on the count sheet that were never entered
    // -----------------------------------------------------------------------

    /**
     * A count line with a 'create' block whose batch doesn't exist yet is
     * created on the sheet's date holding the counted quantity: a reception
     * for raw/packaging material, a roasting batch for roasting. Client,
     * employee and type are copied from the latest record of the same item.
     *
     * @return array<int, string> one description per created batch
     */
    private function createMissingBatches(array $count): array
    {
        $created = [];
        $note = 'Added from closing stock count '.Carbon::parse($this->date)->format('d/m/Y');

        foreach ($count['raw'] ?? [] as $line) {
            $spec = $line['create'] ?? null;
            if (! $spec || $this->matchBatch((string) $line['batch'], $line['item'] ?? null, $this->rawCandidates())->isNotEmpty()) {
                continue;
            }

            $template = RawMaterialStock::whereRaw('LOWER(TRIM(item)) = ?', [mb_strtolower($spec['item'])])->latest('date')->first();

            RawMaterialStock::create([
                'date'         => $spec['date'],
                'item'         => $spec['item'],
                'type'         => $spec['type'] ?? $template?->type ?? 'Raw Material',
                'received'     => (float) $line['qty'],
                'rejected'     => 0,
                'batch_number' => $spec['batch_number'],
                'client_id'    => $template?->client_id ?? DB::table('clients')->value('id'),
                'employee_id'  => $template?->employee_id ?? DB::table('employees')->value('id'),
                'comment'      => $note,
            ]);
            $created[] = "Reception {$spec['batch_number']} ({$spec['item']}, {$spec['date']}): {$this->fmt((float) $line['qty'])}";
        }

        foreach ($count['roasting'] ?? [] as $line) {
            $spec = $line['create'] ?? null;
            if (! $spec || $this->matchBatch((string) $line['batch'], $line['item'] ?? null, $this->roastingCandidates())->isNotEmpty()) {
                continue;
            }

            $source = $this->matchBatch((string) $spec['source_batch'], $line['item'] ?? null, $this->rawCandidates())->first();
            $template = Roasting::latest('date')->first();

            // Inserted directly: Roasting's model events would draw the
            // quantity from the source batch, which the count already covers.
            DB::table('roastings')->insert([
                'date'                  => $spec['date'],
                'batch'                 => $spec['batch'],
                'quantity_in'           => (float) $line['qty'],
                'loss'                  => 0,
                'quantity_remaining'    => (float) $line['qty'],
                'raw_material_stock_id' => $source['id'] ?? null,
                'chef_id'               => $template?->chef_id ?? DB::table('employees')->value('id'),
                'supervisor_id'         => $template?->supervisor_id ?? DB::table('employees')->value('id'),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
            $created[] = "Roasting {$spec['batch']} ({$spec['date']}): {$this->fmt((float) $line['qty'])}";
        }

        return $created;
    }

    // -----------------------------------------------------------------------
    // Movement history
    // -----------------------------------------------------------------------

    private function loadDraws(): void
    {
        foreach (Sorting::get(['id', 'date', 'raw_material_stock_id', 'quantity_in']) as $s) {
            $this->addDraw(StockAdjustment::TYPE_RAW, $s->raw_material_stock_id, $s->date, $s->quantity_in);
        }

        foreach (Roasting::get(['id', 'date', 'raw_material_stock_id', 'sorting_id', 'quantity_in']) as $r) {
            if ($r->raw_material_stock_id) {
                $this->addDraw(StockAdjustment::TYPE_RAW, $r->raw_material_stock_id, $r->date, $r->quantity_in);
            } elseif ($r->sorting_id) {
                $this->addDraw(StockAdjustment::TYPE_SORTING, $r->sorting_id, $r->date, $r->quantity_in);
            }
        }

        $millingSources = [
            'raw'      => StockAdjustment::TYPE_RAW,
            'sorting'  => StockAdjustment::TYPE_SORTING,
            'roasting' => StockAdjustment::TYPE_ROASTING,
        ];
        foreach (Milling::get(['id', 'date', 'items']) as $m) {
            foreach ($m->items ?? [] as $item) {
                $type = $millingSources[$item['source'] ?? ''] ?? null;
                if ($type) {
                    $this->addDraw($type, $item['stock_id'] ?? null, $m->date, $item['quantity'] ?? 0);
                }
            }
        }

        $soldAll = PackagedUnits::soldByEmballage();
        $adjustedAll = PackagedUnits::adjustedByEmballage();

        foreach (Emballage::with('packagingCatalog')->get() as $e) {
            $this->packedUnits[$e->id] = PackagedUnits::original($e, $soldAll, $adjustedAll);
            foreach (PackagedUnits::materialDraws($e, $this->packedUnits[$e->id]) as [$stockId, $units]) {
                $this->addDraw(StockAdjustment::TYPE_RAW, $stockId, $e->date, $units);
            }

            $this->addDraw(StockAdjustment::TYPE_MILLING, $e->milling_id, $e->date, $e->primaryFlourKg());
            foreach ($e->milling_overflow ?? [] as $ov) {
                $this->addDraw(StockAdjustment::TYPE_MILLING, $ov['milling_id'] ?? null, $e->date, $ov['quantity'] ?? 0);
            }
        }

        foreach (Sale::get() as $sale) {
            foreach (Sale::netBatchRestoreMap($sale) as $emballageId => $units) {
                $this->addDraw(StockAdjustment::TYPE_PACKAGED, $emballageId, $sale->date, $units);
            }
        }
    }

    private function loadAdjustments(): void
    {
        foreach (StockAdjustment::get() as $a) {
            $date = $a->date->toDateString();
            // This date's own count adjustments are what we're about to recompute.
            $isThisCount = $date === $this->date && $a->reason === StockAdjustment::REASON_CLOSING_COUNT;
            $this->adjustments["{$a->stock_type}:{$a->stock_id}"][] = [$date, (float) $a->quantity_change, $isThisCount];
        }
    }

    private function addDraw(string $type, mixed $id, mixed $date, mixed $qty): void
    {
        $id = (int) $id;
        $qty = (float) $qty;
        if (! $id || abs($qty) < self::EPSILON || ! $date) {
            return;
        }

        $this->draws["{$type}:{$id}"][] = [Carbon::parse($date)->toDateString(), $qty];
    }

    /** Draws dated on/before the count date ($after = false) or after it ($after = true). */
    private function drawn(string $key, bool $after): float
    {
        return (float) collect($this->draws[$key] ?? [])
            ->filter(fn ($d) => $after ? $d[0] > $this->date : $d[0] <= $this->date)
            ->sum(fn ($d) => $d[1]);
    }

    private function adjusted(string $key, bool $after, bool $includeThisCount = false): float
    {
        return (float) collect($this->adjustments[$key] ?? [])
            ->filter(fn ($a) => $includeThisCount || ! $a[2])
            ->filter(fn ($a) => $after ? $a[0] > $this->date : $a[0] <= $this->date)
            ->sum(fn ($a) => $a[1]);
    }

    // -----------------------------------------------------------------------
    // Batches in scope (dated on or before the count date)
    // -----------------------------------------------------------------------

    private function rawCandidates(): array
    {
        // Every type, "Other" included: the count sheet covers all stock.
        return RawMaterialStock::whereDate('date', '<=', $this->date)
            ->get()
            ->map(fn (RawMaterialStock $r) => [
                'id'      => $r->id,
                'ref'     => (string) $r->batch_number,
                'item'    => (string) $r->item,
                'date'    => $r->date->toDateString(),
                'initial' => $r->initialNetQuantity(),
                'live'    => (float) $r->quantity_in,
            ])
            ->all();
    }

    private function sortingCandidates(): array
    {
        return Sorting::with('rawMaterialStock')
            ->whereDate('date', '<=', $this->date)
            ->get()
            ->map(fn (Sorting $s) => [
                'id'      => $s->id,
                'ref'     => (string) $s->rawMaterialStock?->batch_number,
                'item'    => (string) $s->rawMaterialStock?->item,
                'date'    => $s->date->toDateString(),
                'initial' => $s->quantityOut(),
                'live'    => $s->remainingUsable(),
            ])
            ->all();
    }

    private function roastingCandidates(): array
    {
        return Roasting::with(['rawMaterialStock', 'sorting.rawMaterialStock'])
            ->whereDate('date', '<=', $this->date)
            ->get()
            ->map(fn (Roasting $r) => [
                'id'      => $r->id,
                'ref'     => (string) $r->batch,
                'item'    => (string) ($r->rawMaterialStock?->item ?? $r->sorting?->rawMaterialStock?->item),
                'date'    => $r->date->toDateString(),
                'initial' => $r->quantityOut(),
                'live'    => $r->remainingUsable(),
            ])
            ->all();
    }

    private function millingCandidates(): array
    {
        return Milling::whereDate('date', '<=', $this->date)
            ->get()
            ->map(fn (Milling $m) => [
                'id'      => $m->id,
                'ref'     => (string) $m->batch_number,
                'item'    => 'Flour',
                'date'    => $m->date->toDateString(),
                // Same basis as the monthly report's flour balance.
                'initial' => max((float) $m->total_mixed_quantity - (float) $m->loss, 0),
                'live'    => (float) $m->output_flour,
            ])
            ->all();
    }

    private function packagedCandidates(): array
    {
        return Emballage::with('packagingCatalog')
            ->whereDate('date', '<=', $this->date)
            ->get()
            ->map(fn (Emballage $e) => [
                'id'      => $e->id,
                'ref'     => (string) $e->batch,
                'item'    => $e->packagingCatalog?->name ?? strtoupper((string) $e->packaging_type),
                'date'    => $e->date->toDateString(),
                'initial' => $this->packedUnits[$e->id] ?? (float) $e->item,
                'live'    => (float) $e->item,
            ])
            ->all();
    }

    // -----------------------------------------------------------------------
    // Planning
    // -----------------------------------------------------------------------

    /**
     * @param  array<int, array<string, mixed>>  $candidates
     * @param  array<int, array<string, mixed>>  $lines  [['batch' => ..., 'qty' => ..., 'item' => ?], ...]
     */
    private function planByBatch(string $type, array $candidates, array $lines): array
    {
        $byId = collect($candidates)->keyBy('id');
        $counted = [];
        $refs = [];

        foreach ($lines as $line) {
            $code = (string) ($line['batch'] ?? '');
            $matches = $this->matchBatch($code, $line['item'] ?? null, $candidates);

            if ($matches->isEmpty()) {
                $this->errors[] = sprintf('%s: no batch matches "%s"%s.', self::SECTION_TITLES[$type], $code,
                    isset($line['item']) ? " (item: {$line['item']})" : '');

                continue;
            }

            if ($matches->pluck('ref')->map(fn ($r) => $this->normalize($r))->unique()->count() > 1) {
                $this->errors[] = sprintf('%s: "%s" matches several batches (%s). Write the full batch number.',
                    self::SECTION_TITLES[$type], $code, $matches->pluck('ref')->unique()->implode(', '));

                continue;
            }

            foreach ($this->allocate($matches, (float) $line['qty']) as $id => $qty) {
                if (isset($counted[$id])) {
                    $this->errors[] = sprintf('%s: batch %s is listed more than once.', self::SECTION_TITLES[$type], $byId[$id]['ref']);
                }
                $counted[$id] = $qty;
                $refs[$id] = $code;
            }
        }

        return $this->buildRows($type, $candidates, $counted, $refs, []);
    }

    /**
     * @param  array<int, array<string, mixed>>  $candidates
     * @param  array<int, array<string, mixed>>  $lines  [['match' => [...], 'units' => ..., 'notes' => ?], ...]
     */
    private function planPackaged(array $candidates, array $lines): array
    {
        $groups = array_fill(0, count($lines), []);

        foreach ($candidates as $c) {
            $name = $this->normalize($c['item']);
            foreach ($lines as $i => $line) {
                $words = array_map(fn ($w) => $this->normalize($w), (array) $line['match']);
                if (collect($words)->contains(fn ($w) => $w !== '' && str_contains($name, $w))) {
                    $groups[$i][] = $c;
                    break;
                }
            }
        }

        $counted = [];
        $refs = [];
        $notes = [];
        foreach ($lines as $i => $line) {
            $label = implode('/', (array) $line['match']);
            if (empty($groups[$i])) {
                if ((float) $line['units'] > 0) {
                    $this->errors[] = "Packaged flour: no packaging batch matches \"{$label}\".";
                }

                continue;
            }
            foreach ($this->allocate(collect($groups[$i]), (float) $line['units']) as $id => $qty) {
                $counted[$id] = $qty;
                $refs[$id] = $label;
                $notes[$id] = $line['notes'] ?? null;
            }
        }

        return $this->buildRows(StockAdjustment::TYPE_PACKAGED, $candidates, $counted, $refs, $notes);
    }

    /**
     * Batch numbers in the app carry a date prefix ("15SeptM2602130") that
     * the count sheet leaves out ("M2602130"), so a batch matches when its
     * normalised number ends with the code. Failing that, the digits alone
     * are matched the same way, limited to batches whose item contains $item.
     */
    private function matchBatch(string $code, ?string $item, array $candidates): \Illuminate\Support\Collection
    {
        $pool = collect($candidates)->filter(fn ($c) => $c['ref'] !== '');
        $wanted = $this->normalize($code);

        $byCode = $pool->filter(fn ($c) => $wanted !== '' && str_ends_with($this->normalize($c['ref']), $wanted));
        if ($byCode->isNotEmpty()) {
            return $byCode->values();
        }

        $digits = preg_replace('/\D/', '', $code);
        if ($digits === '') {
            return collect();
        }

        return $pool
            ->filter(fn ($c) => str_ends_with(preg_replace('/\D/', '', $c['ref']), $digits))
            ->filter(fn ($c) => ! $item || str_contains($this->normalize($c['item']), $this->normalize($item)))
            ->values();
    }

    /**
     * Spread a counted quantity over the batches it matched. Older stock is
     * used first, so what's left is assumed to sit in the newest batches;
     * each takes up to what it originally held, any excess goes to the newest.
     *
     * @return array<int, float> id => counted
     */
    private function allocate(\Illuminate\Support\Collection $matches, float $qty): array
    {
        $ordered = $matches->sortByDesc(fn ($c) => $c['date'].'-'.str_pad((string) $c['id'], 10, '0', STR_PAD_LEFT))->values();
        $result = [];
        $left = $qty;

        foreach ($ordered as $c) {
            $take = min($left, max((float) $c['initial'], 0));
            $result[$c['id']] = $take;
            $left -= $take;
        }

        if ($left > self::EPSILON) {
            $result[$ordered[0]['id']] += $left;
        }

        return $result;
    }

    private function buildRows(string $type, array $candidates, array $counted, array $refs, array $notes): array
    {
        return collect($candidates)
            ->map(function ($c) use ($type, $counted, $refs, $notes) {
                $key = "{$type}:{$c['id']}";
                $book = (float) $c['initial'] - $this->drawn($key, false) + $this->adjusted($key, false);
                $count = (float) ($counted[$c['id']] ?? 0);
                $liveNew = $count - $this->drawn($key, true) + $this->adjusted($key, true, true);

                $warning = null;
                if ($liveNew < -self::EPSILON) {
                    $warning = sprintf('%s batch %s (#%d): more was used after %s than was counted; live balance set to 0.',
                        self::SECTION_TITLES[$type], $c['ref'] ?: '—', $c['id'], $this->date);
                    $liveNew = 0.0;
                }

                return $c + [
                    'type'      => $type,
                    'book'      => round($book, 3),
                    'counted'   => round($count, 3),
                    'live_new'  => round($liveNew, 3),
                    'reference' => $refs[$c['id']] ?? null,
                    'notes'     => $notes[$c['id']] ?? null,
                    'warning'   => $warning,
                ];
            })
            ->sortBy([['item', 'asc'], ['date', 'asc']])
            ->values()
            ->all();
    }

    // -----------------------------------------------------------------------
    // Output & writing
    // -----------------------------------------------------------------------

    private function report(array $plan, array $created): void
    {
        $this->info("Closing stock count as of {$this->date}");

        if ($created) {
            $this->newLine();
            $this->line('<options=bold>Batches on the sheet that were missing, created with the counted quantity</>');
            foreach ($created as $line) {
                $this->line("  + {$line}");
            }
        }

        foreach ($plan as $type => $rows) {
            $visible = collect($rows)->filter(fn ($r) => abs($r['book']) >= self::EPSILON
                || abs($r['counted']) >= self::EPSILON
                || abs($r['live']) >= self::EPSILON
                || abs($r['live_new']) >= self::EPSILON);

            $this->newLine();
            $this->line('<options=bold>'.self::SECTION_TITLES[$type].'</>');

            if ($visible->isEmpty()) {
                $this->line('  Nothing in stock, nothing to change.');

                continue;
            }

            $this->table(
                ['#', 'Batch', 'Item', 'Date', "Book {$this->date}", 'Counted', 'Adjustment', 'Live now', 'Live after'],
                $visible->map(fn ($r) => [
                    $r['id'],
                    $r['ref'] ?: '—',
                    $r['item'],
                    $r['date'],
                    $this->fmt($r['book']),
                    $this->fmt($r['counted']),
                    $this->fmt($r['counted'] - $r['book'], true),
                    $this->fmt($r['live']),
                    $this->fmt($r['live_new']),
                ])->all()
            );
        }

        foreach (collect($plan)->flatten(1)->pluck('warning')->filter() as $warning) {
            $this->warn($warning);
        }
    }

    /** @return array{0: int, 1: int} */
    private function write(array $plan): array
    {
        StockAdjustment::where('reason', StockAdjustment::REASON_CLOSING_COUNT)
            ->whereDate('date', $this->date)
            ->delete();

        $adjusted = 0;
        $updated = 0;

        foreach ($plan as $type => $rows) {
            [$table, $column] = self::LIVE_COLUMNS[$type];

            foreach ($rows as $r) {
                $change = round($r['counted'] - $r['book'], 3);
                if (abs($change) >= self::EPSILON) {
                    StockAdjustment::create([
                        'date'             => $this->date,
                        'stock_type'       => $type,
                        'stock_id'         => $r['id'],
                        'book_quantity'    => $r['book'],
                        'counted_quantity' => $r['counted'],
                        'quantity_change'  => $change,
                        'reference'        => $r['reference'] ?? $r['ref'],
                        'reason'           => StockAdjustment::REASON_CLOSING_COUNT,
                        'notes'            => $r['notes'],
                    ]);
                    $adjusted++;
                }

                // Direct update on purpose: model events would re-derive these balances.
                if (abs($r['live_new'] - $r['live']) >= self::EPSILON) {
                    DB::table($table)->where('id', $r['id'])->update([$column => $r['live_new']]);
                    $updated++;
                }
            }
        }

        return [$adjusted, $updated];
    }

    private function normalize(?string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $value));
    }

    private function fmt(float $value, bool $signed = false): string
    {
        if (abs($value) < self::EPSILON) {
            return $signed ? '—' : '0';
        }

        $formatted = rtrim(rtrim(number_format($value, 3, '.', ','), '0'), '.');

        return $signed && $value > 0 ? "+{$formatted}" : $formatted;
    }
}
