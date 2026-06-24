<?php

namespace App\Support\Inventory;

use App\Models\Emballage;
use App\Models\Roasting;
use App\Models\Sale;
use App\Models\Sorting;

class InventoryReferences
{
    public static function rawMaterialStockInUse(int $stockId): bool
    {
        return self::rawMaterialStockUsageReason($stockId) !== null;
    }

    public static function rawMaterialStockUsageReason(int $stockId): ?string
    {
        if (Sorting::query()->where('raw_material_stock_id', $stockId)->exists()) {
            return 'Cannot delete: this batch is used in sorting records.';
        }

        if (Roasting::query()->where('raw_material_stock_id', $stockId)->exists()) {
            return 'Cannot delete: this batch is used in roasting records.';
        }

        if (Emballage::query()->where('raw_material_stock_id', $stockId)->exists()) {
            return 'Cannot delete: this batch is used as packaging material.';
        }

        if (Emballage::query()->where('envelope_stock_id', $stockId)->exists()) {
            return 'Cannot delete: this batch is used as envelope stock for box packaging.';
        }

        return null;
    }

    public static function emballageReferencedInSale(int $emballageId): bool
    {
        foreach (Sale::query()->select(['id', 'batches'])->cursor() as $sale) {
            foreach ($sale->batches ?? [] as $batch) {
                if (! is_array($batch)) {
                    continue;
                }

                if ((int) ($batch['emballage_id'] ?? 0) === $emballageId) {
                    return true;
                }
            }
        }

        return false;
    }
}
