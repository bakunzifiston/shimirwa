<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'item',
        'batches',      // JSON [{emballage_id, quantity, unit_price, line_total}]
        'quantity',     // total quantity
        'total_price',  // total price
        'client_id',
        'employee_id',
        'returned',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'batches' => 'array',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // Relationships
    public function client() { return $this->belongsTo(Client::class); }
    public function employee() { return $this->belongsTo(Employee::class); }

    protected static function booted()
    {
        parent::booted();

        // Auto-calculate quantity and total before save
        static::saving(function ($sale) {
            if (is_array($sale->batches)) {
                $sale->quantity = collect($sale->batches)
                    ->sum(fn($b) => isset($b['quantity']) && is_numeric($b['quantity']) ? (int)$b['quantity'] : 0);

                $sale->total_price = collect($sale->batches)
                    ->sum(fn($b) => isset($b['line_total']) && is_numeric($b['line_total']) ? (float)$b['line_total'] : 0);
            }
        });

        // Deduct stock after create
        static::created(function ($sale) {
            DB::transaction(function () use ($sale) {
                foreach ($sale->batches ?? [] as $entry) {
                    $emb = Emballage::lockForUpdate()->find($entry['emballage_id'] ?? null);
                    $qty = isset($entry['quantity']) && is_numeric($entry['quantity']) ? (int)$entry['quantity'] : 0;
                    if ($emb && $qty > 0) {
                        if ($qty > $emb->item) {
                            throw new Exception("Not enough stock in batch {$emb->batch}");
                        }
                        self::adjustEmballageItem($emb, -$qty);
                    }
                }

                $returned = (int) ($sale->returned ?? 0);
                if ($returned > 0) {
                    self::applyReturnedDelta($sale, $returned);
                }
            });
        });

        // Adjust stock after update
        static::updated(function ($sale) {
            DB::transaction(function () use ($sale) {
                $original = $sale->getOriginal('batches');
                $originalBatches = is_array($original) ? $original : (json_decode($original ?? '[]', true) ?: []);
                $newBatches = $sale->batches ?? [];

                $oldMap = []; $newMap = [];
                foreach ($originalBatches as $b) { $oldMap[$b['emballage_id']] = $b['quantity'] ?? 0; }
                foreach ($newBatches as $b) { $newMap[$b['emballage_id']] = $b['quantity'] ?? 0; }

                $allIds = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));
                foreach ($allIds as $id) {
                    $oldQty = $oldMap[$id] ?? 0;
                    $newQty = $newMap[$id] ?? 0;
                    $diff = $newQty - $oldQty;

                    $emb = Emballage::lockForUpdate()->find($id);
                    if ($emb) {
                        if ($diff > 0 && $diff > $emb->item) {
                            throw new Exception("Not enough stock in batch {$emb->batch}");
                        }
                        if ($diff > 0) {
                            self::adjustEmballageItem($emb, -$diff);
                        }
                        if ($diff < 0) {
                            self::adjustEmballageItem($emb, abs($diff));
                        }
                    }
                }

                if ($sale->wasChanged('returned')) {
                    $delta = (int) $sale->returned - (int) $sale->getOriginal('returned');
                    if ($delta !== 0) {
                        self::applyReturnedDelta($sale, $delta);
                    }
                }
            });
        });

        // Restore stock after delete
        static::deleted(function ($sale) {
            DB::transaction(function () use ($sale) {
                foreach (self::netBatchRestoreMap($sale) as $emballageId => $qty) {
                    if ($qty <= 0) {
                        continue;
                    }

                    Emballage::withoutEvents(function () use ($emballageId, $qty) {
                        Emballage::lockForUpdate()->find($emballageId)?->increment('item', $qty);
                    });
                }
            });
        });
    }

    /**
     * Net units still deducted from each packaging batch after returns.
     *
     * @return array<int, int>
     */
    private static function netBatchRestoreMap(Sale $sale): array
    {
        $batches = $sale->batches ?? [];
        $returned = min((int) ($sale->returned ?? 0), (int) collect($batches)->sum(fn ($b) => (int) ($b['quantity'] ?? 0)));
        $remainingReturn = $returned;
        $map = [];

        foreach (array_reverse($batches) as $entry) {
            $emballageId = (int) ($entry['emballage_id'] ?? 0);
            $sold = (int) ($entry['quantity'] ?? 0);
            if (! $emballageId || $sold <= 0) {
                continue;
            }

            $returnFromBatch = min($remainingReturn, $sold);
            $net = max($sold - $returnFromBatch, 0);
            $map[$emballageId] = ($map[$emballageId] ?? 0) + $net;
            $remainingReturn -= $returnFromBatch;
        }

        return $map;
    }

    /**
     * Positive delta restores units to packaging batches; negative re-deducts.
     */
    private static function applyReturnedDelta(Sale $sale, int $delta): void
    {
        $batches = $sale->batches ?? [];

        if ($delta > 0) {
            $remaining = $delta;
            foreach (array_reverse($batches) as $entry) {
                $embId = (int) ($entry['emballage_id'] ?? 0);
                $sold = (int) ($entry['quantity'] ?? 0);
                if (! $embId || $sold <= 0) {
                    continue;
                }

                $restore = min($remaining, $sold);
                self::adjustEmballageItem(
                    Emballage::query()->lockForUpdate()->find($embId),
                    $restore
                );
                $remaining -= $restore;

                if ($remaining <= 0) {
                    break;
                }
            }

            return;
        }

        $remaining = abs($delta);
        foreach ($batches as $entry) {
            $embId = (int) ($entry['emballage_id'] ?? 0);
            $sold = (int) ($entry['quantity'] ?? 0);
            if (! $embId || $sold <= 0) {
                continue;
            }

            $emb = Emballage::query()->lockForUpdate()->find($embId);
            if (! $emb) {
                continue;
            }

            $deduct = min($remaining, $sold);
            if ($deduct > $emb->item) {
                throw new Exception("Cannot reduce returned count: not enough stock in batch {$emb->batch}.");
            }

            self::adjustEmballageItem($emb, -$deduct);
            $remaining -= $deduct;

            if ($remaining <= 0) {
                break;
            }
        }
    }

    private static function adjustEmballageItem(?Emballage $emballage, int $delta): void
    {
        if (! $emballage || $delta === 0) {
            return;
        }

        Emballage::withoutEvents(function () use ($emballage, $delta) {
            if ($delta > 0) {
                $emballage->increment('item', $delta);
            } else {
                $emballage->decrement('item', abs($delta));
            }
        });
    }
}
