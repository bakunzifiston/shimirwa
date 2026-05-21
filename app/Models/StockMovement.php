<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const REASON_ORDER_PLACED = 'order_placed';

    public const REASON_ORDER_CANCELLED = 'order_cancelled';

    public const REASON_MANUAL = 'manual_adjustment';

    protected $fillable = [
        'product_id',
        'shop_order_id',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'shop_order_id');
    }
}
