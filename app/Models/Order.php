<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'shop_orders';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_CANCELLED = 'cancelled';

    public const STATUS_NEW = 'new';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_number',
        'customer_id',
        'subtotal',
        'total',
        'payment_status',
        'order_status',
        'idempotency_key',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'shop_order_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'shop_order_id');
    }

    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_PENDING => 'Pending',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_CANCELLED => 'Cancelled',
        ];
    }

    public static function orderStatuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function isStockDeducted(): bool
    {
        return in_array($this->order_status, [
            self::STATUS_NEW,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_COMPLETED,
        ], true) && $this->payment_status !== self::PAYMENT_CANCELLED;
    }
}
