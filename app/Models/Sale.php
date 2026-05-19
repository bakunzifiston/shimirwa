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
                        $emb->decrement('item', $qty);
                    }
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
                            $emb->decrement('item', $diff);
                        }
                        if ($diff < 0) {
                            $emb->increment('item', abs($diff));
                        }
                    }
                }
            });
        });

        // Restore stock after delete
        static::deleted(function ($sale) {
            DB::transaction(function () use ($sale) {
                foreach ($sale->batches ?? [] as $entry) {
                    $emb = Emballage::lockForUpdate()->find($entry['emballage_id'] ?? null);
                    $qty = isset($entry['quantity']) && is_numeric($entry['quantity']) ? (int)$entry['quantity'] : 0;
                    if ($emb && $qty > 0) {
                        $emb->increment('item', $qty);
                    }
                }
            });
        });
    }
}
