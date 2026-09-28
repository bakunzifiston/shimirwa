<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfills sortings.quantity_remaining and roastings.quantity_remaining for
 * existing rows, and restores sortings.quantity_in to its true gross value.
 *
 * Context: Roasting batches sourced from a Sorting batch used to decrement
 * sortings.quantity_in directly, while Milling batches sourced from a
 * Sorting batch decrement sortings.quantity_remaining instead — two
 * different columns for the same "how much is left" concept, which could
 * drift out of sync. Going forward both consumers use quantity_remaining
 * consistently (see App\Models\Roasting::drawFromSorting), and quantity_in
 * stays a fixed historical "gross input" figure as originally documented.
 *
 * This migration is idempotent: it only touches rows where
 * quantity_remaining is still NULL (i.e. never touched by the old
 * mechanism or by Milling), so re-running it is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->backfillSortings();
        $this->backfillRoastings();
    }

    private function backfillSortings(): void
    {
        // How much each Sorting batch has already been drawn down by roastings
        // under the old mechanism (which decremented sortings.quantity_in).
        $roastingConsumedBySorting = DB::table('roastings')
            ->whereNotNull('sorting_id')
            ->selectRaw('sorting_id, SUM(quantity_in) as consumed')
            ->groupBy('sorting_id')
            ->pluck('consumed', 'sorting_id');

        // How much each Sorting batch has already been drawn down directly by
        // milling (source: 'sorting'), tracked via items JSON.
        $millingConsumedBySorting = $this->millingConsumedBySource('sorting');

        $sortings = DB::table('sortings')->whereNull('quantity_remaining')->get();

        foreach ($sortings as $sorting) {
            $roastingConsumed = (float) ($roastingConsumedBySorting[$sorting->id] ?? 0);
            $millingConsumed  = (float) ($millingConsumedBySorting[$sorting->id] ?? 0);

            // Current quantity_in already had roasting consumption subtracted
            // from it by the old mechanism — add it back to recover the true
            // original gross figure.
            $trueGross = (float) $sorting->quantity_in + $roastingConsumed;
            $remaining = max($trueGross - (float) $sorting->loss - $roastingConsumed - $millingConsumed, 0);

            DB::table('sortings')
                ->where('id', $sorting->id)
                ->update([
                    'quantity_in'        => $trueGross,
                    'quantity_remaining' => $remaining,
                ]);
        }
    }

    private function backfillRoastings(): void
    {
        // Roasting's own quantity_in has always been the pure gross figure —
        // only Milling (source: 'roasting') ever draws from a Roasting batch,
        // and it already does so via quantity_remaining correctly. This just
        // catches batches Milling has never touched (still NULL).
        $millingConsumedByRoasting = $this->millingConsumedBySource('roasting');

        $roastings = DB::table('roastings')->whereNull('quantity_remaining')->get();

        foreach ($roastings as $roasting) {
            $millingConsumed = (float) ($millingConsumedByRoasting[$roasting->id] ?? 0);
            $remaining = max((float) $roasting->quantity_in - (float) $roasting->loss - $millingConsumed, 0);

            DB::table('roastings')
                ->where('id', $roasting->id)
                ->update(['quantity_remaining' => $remaining]);
        }
    }

    /**
     * Sum milling ingredient quantities per source stock id, for one source
     * type ('sorting' or 'roasting'), parsed from the items JSON column.
     *
     * @return array<int, float>
     */
    private function millingConsumedBySource(string $source): array
    {
        $totals = [];

        DB::table('millings')->select('items')->orderBy('id')->get()->each(function ($milling) use ($source, &$totals) {
            $items = json_decode($milling->items ?? '[]', true) ?: [];
            foreach ($items as $item) {
                if (($item['source'] ?? '') !== $source) {
                    continue;
                }
                $stockId = (int) ($item['stock_id'] ?? 0);
                if (! $stockId) {
                    continue;
                }
                $totals[$stockId] = ($totals[$stockId] ?? 0) + (float) ($item['quantity'] ?? 0);
            }
        });

        return $totals;
    }

    public function down(): void
    {
        // Not reversible: the old quantity_in semantics (net of roasting
        // consumption) can't be reliably reconstructed from the restored
        // gross figure once other batches have moved on.
    }
};
