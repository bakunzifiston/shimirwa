<?php

namespace App\Support\Inventory;

use App\Models\Emballage;
use App\Models\Sale;
use App\Models\StockAdjustment;

/**
 * emballages.item is a live unit count: sales and stock-count adjustments
 * change it in place. Anything that needs the number of units originally
 * packed (e.g. how many boxes and inner bags were used) must add those
 * movements back, which these helpers do.
 */
class PackagedUnits
{
    /**
     * Net units sold (after returns) per emballage id, optionally only from
     * sales dated after $afterDate.
     *
     * @return array<int, float>
     */
    public static function soldByEmballage(?string $afterDate = null): array
    {
        $sold = [];

        Sale::query()
            ->when($afterDate, fn ($q) => $q->whereDate('date', '>', $afterDate))
            ->get()
            ->each(function (Sale $sale) use (&$sold) {
                foreach (Sale::netBatchRestoreMap($sale) as $emballageId => $units) {
                    $sold[$emballageId] = ($sold[$emballageId] ?? 0) + $units;
                }
            });

        return $sold;
    }

    /**
     * Stock-count adjustments per emballage id, optionally only those dated
     * after $afterDate.
     *
     * @return array<int, float>
     */
    public static function adjustedByEmballage(?string $afterDate = null): array
    {
        return StockAdjustment::where('stock_type', StockAdjustment::TYPE_PACKAGED)
            ->when($afterDate, fn ($q) => $q->whereDate('date', '>', $afterDate))
            ->selectRaw('stock_id, SUM(quantity_change) as qty')
            ->groupBy('stock_id')
            ->pluck('qty', 'stock_id')
            ->map(fn ($qty) => (float) $qty)
            ->all();
    }

    /**
     * Units originally packed in this emballage batch.
     *
     * @param  array<int, float>  $soldAll      from soldByEmballage()
     * @param  array<int, float>  $adjustedAll  from adjustedByEmballage()
     */
    public static function original(Emballage $emballage, array $soldAll, array $adjustedAll): float
    {
        return (float) $emballage->item
            + (float) ($soldAll[$emballage->id] ?? 0)
            - (float) ($adjustedAll[$emballage->id] ?? 0);
    }

    /**
     * Packaging-material draws this batch made when it was packed, based on
     * the original unit count: [[raw_material_stock_id, units], ...] for the
     * primary batch, overflow batches and inner units (e.g. bags in boxes).
     *
     * @return array<int, array{0: int, 1: float}>
     */
    public static function materialDraws(Emballage $emballage, float $originalUnits): array
    {
        $draws = [];

        $overflowUnits = $emballage->packagingOverflowUnits();
        if ($emballage->raw_material_stock_id) {
            $draws[] = [(int) $emballage->raw_material_stock_id, max($originalUnits - $overflowUnits, 0)];
        }
        foreach ($emballage->packaging_overflow ?? [] as $ov) {
            if (! empty($ov['stock_id'])) {
                $draws[] = [(int) $ov['stock_id'], (float) ($ov['units'] ?? 0)];
            }
        }

        $catalog = $emballage->packagingCatalog;
        if ($emballage->inner_stock_id && $catalog && $catalog->hasInnerUnits()) {
            $draws[] = [(int) $emballage->inner_stock_id, (float) ((int) round($originalUnits) * (int) $catalog->inner_units_per_package)];
        }

        return array_values(array_filter($draws, fn ($d) => $d[1] > 0));
    }
}
