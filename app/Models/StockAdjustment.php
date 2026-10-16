<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A correction to one batch's balance, recorded from a physical stock count.
 *
 * stock_type + stock_id point at the corrected batch:
 *   raw      → raw_material_stocks (quantity_in)
 *   sorting  → sortings            (quantity_remaining)
 *   roasting → roastings           (quantity_remaining)
 *   milling  → millings            (output_flour)
 *   packaged → emballages          (item)
 */
class StockAdjustment extends Model
{
    public const TYPE_RAW = 'raw';

    public const TYPE_SORTING = 'sorting';

    public const TYPE_ROASTING = 'roasting';

    public const TYPE_MILLING = 'milling';

    public const TYPE_PACKAGED = 'packaged';

    public const REASON_CLOSING_COUNT = 'closing_count';

    protected $fillable = [
        'date',
        'stock_type',
        'stock_id',
        'book_quantity',
        'counted_quantity',
        'quantity_change',
        'reference',
        'reason',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'book_quantity' => 'float',
        'counted_quantity' => 'float',
        'quantity_change' => 'float',
    ];
}
