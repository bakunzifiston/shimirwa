<?php

namespace App\Support\Inventory;

use App\Models\Emballage;
use App\Models\Milling;
use App\Models\Roasting;

class MillingItemUsage
{
    public static function sortingReferenced(int $sortingId): bool
    {
        return self::scanItems(function (array $item) use ($sortingId): bool {
            $type = strtolower((string) ($item['type'] ?? ''));

            if (in_array($type, ['soy', 'maize'], true)) {
                return false;
            }

            return (int) ($item['stock_id'] ?? 0) === $sortingId;
        });
    }

    public static function roastingReferenced(int $roastingId): bool
    {
        return self::scanItems(function (array $item) use ($roastingId): bool {
            $type = strtolower((string) ($item['type'] ?? ''));

            if (! in_array($type, ['soy', 'maize'], true)) {
                return false;
            }

            return (int) ($item['stock_id'] ?? 0) === $roastingId;
        });
    }

    public static function millingReferencedInEmballage(int $millingId): bool
    {
        return Emballage::query()->where('milling_id', $millingId)->exists();
    }

    public static function sortingReferencedInRoasting(int $sortingId): bool
    {
        return Roasting::query()->where('sorting_id', $sortingId)->exists();
    }

    /**
     * @param  callable(array<string, mixed>): bool  $matcher
     */
    private static function scanItems(callable $matcher): bool
    {
        foreach (Milling::query()->select(['id', 'items'])->cursor() as $milling) {
            foreach ($milling->items ?? [] as $item) {
                if (! is_array($item)) {
                    continue;
                }

                if ($matcher($item)) {
                    return true;
                }
            }
        }

        return false;
    }
}
