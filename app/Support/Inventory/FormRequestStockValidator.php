<?php

namespace App\Support\Inventory;

use App\Models\Emballage;
use App\Models\Milling;
use App\Models\RawMaterialStock;
use App\Models\Roasting;
use App\Models\Sale;
use App\Models\Sorting;

class FormRequestStockValidator
{
    /**
     * @return array<string, string> field => message
     */
    public static function roasting(array $data, ?Roasting $existing = null): array
    {
        $errors = [];
        $source = $data['source_type'] ?? 'raw';
        $gross = (float) ($data['quantity_in'] ?? 0);

        if ($source === 'raw') {
            $stockId = (int) ($data['raw_material_stock_id'] ?? 0);
            $stock = RawMaterialStock::query()->find($stockId);
            if (! $stock) {
                $errors['raw_material_stock_id'] = 'Select a valid raw material batch.';

                return $errors;
            }

            $available = $stock->remainingQuantity();
            if ($existing && (int) $existing->raw_material_stock_id === $stockId) {
                $available += (float) $existing->quantity_in;
            }
        } else {
            $sortingId = (int) ($data['sorting_id'] ?? 0);
            $sorting = Sorting::query()->find($sortingId);
            if (! $sorting) {
                $errors['sorting_id'] = 'Select a valid sorting batch.';

                return $errors;
            }

            $available = $sorting->remainingUsable();
            if ($existing && (int) $existing->sorting_id === $sortingId) {
                $available += (float) $existing->quantity_in;
            }
        }

        if ($gross > $available) {
            $field = $source === 'raw' ? 'quantity_in' : 'quantity_in';
            $errors[$field] = sprintf(
                'Not enough stock available. Maximum: %s kg.',
                number_format($available, 2)
            );
        }

        return $errors;
    }

    /**
     * @param  array<int, array<string, mixed>>  $newItems
     * @param  array<int, array<string, mixed>>  $oldItems
     * @return array<string, string>
     */
    public static function millingItems(array $newItems, array $oldItems = []): array
    {
        $errors = [];
        $oldMap = self::millingItemMap($oldItems);
        $newMap = self::millingItemMap($newItems);
        $keys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));

        foreach ($keys as $key) {
            $diff = ($newMap[$key] ?? 0) - ($oldMap[$key] ?? 0);
            if ($diff <= 0) {
                continue;
            }

            [$type, $stockId] = explode(':', $key, 2);
            $batch = self::resolveMillingBatch($type, (int) $stockId);
            if (! $batch) {
                $errors['items'] = 'One or more selected batches do not exist.';

                continue;
            }

            if ($batch->remainingUsable() < $diff) {
                $label = $batch instanceof Roasting
                    ? $batch->batch
                    : ($batch->rawMaterialStock?->batch_number ?? "#{$batch->id}");
                $errors['items'] = "Not enough stock in batch {$label}. Need ".number_format($diff, 2).' kg more.';

                break;
            }
        }

        return $errors;
    }

    public static function millingLoss(float $loss, array $items): ?string
    {
        $total = collect($items)->sum(fn ($item) => (float) ($item['quantity'] ?? 0));
        if ($loss > $total) {
            return 'Loss cannot exceed total mixed quantity ('.number_format($total, 2).' kg).';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public static function emballage(array $data, ?Emballage $existing = null): array
    {
        $errors = [];
        $type = strtolower(trim((string) ($data['packaging_type'] ?? '1kg')));
        $item = (float) ($data['item'] ?? 0);
        $kgNeeded = $type === 'sack'
            ? (float) ($data['quantity'] ?? 0)
            : $item * Emballage::packagingKg($type);

        $rawStock = RawMaterialStock::query()->find((int) ($data['raw_material_stock_id'] ?? 0));
        if (! $rawStock) {
            $errors['raw_material_stock_id'] = 'Select a valid packaging material batch.';

            return $errors;
        }

        $rawAvailable = $rawStock->remainingQuantity();
        if ($existing && (int) $existing->raw_material_stock_id === (int) $rawStock->id) {
            $rawAvailable += (float) $existing->item;
        }

        if ($item > $rawAvailable) {
            $errors['item'] = sprintf(
                'Not enough packaging material. Available: %s units.',
                number_format($rawAvailable, 0)
            );
        }

        if ($type === 'box') {
            $envelope = RawMaterialStock::query()->find((int) ($data['envelope_stock_id'] ?? 0));
            if (! $envelope) {
                $errors['envelope_stock_id'] = 'Select a valid envelope batch for box packaging.';

                return $errors;
            }

            $envelopeNeeded = $item * 12;
            $envelopeAvailable = $envelope->remainingQuantity();
            if ($existing && (int) $existing->envelope_stock_id === (int) $envelope->id) {
                $envelopeAvailable += (float) $existing->item * 12;
            }

            if ($envelopeNeeded > $envelopeAvailable) {
                $errors['envelope_stock_id'] = sprintf(
                    'Not enough envelopes. Need %s, available: %s.',
                    number_format($envelopeNeeded, 0),
                    number_format($envelopeAvailable, 0)
                );
            }
        }

        $millingId = (int) ($data['milling_id'] ?? 0);
        if ($kgNeeded > 0 && $millingId) {
            $milling = Milling::query()->find($millingId);
            if (! $milling) {
                $errors['milling_id'] = 'Select a valid milling batch.';

                return $errors;
            }

            $flourAvailable = (float) $milling->output_flour;
            if ($existing && (int) $existing->milling_id === $millingId) {
                $flourAvailable += (float) $existing->quantity;
            }

            if ($kgNeeded > $flourAvailable) {
                $errors['milling_id'] = sprintf(
                    'Milling batch %s has only %s kg flour available; this packaging needs %s kg.',
                    $milling->batch_number,
                    number_format($flourAvailable, 2),
                    number_format($kgNeeded, 2)
                );
            }
        }

        return $errors;
    }

    /**
     * @param  array<int, array<string, mixed>>  $batches
     * @return array<string, string>
     */
    public static function saleBatches(array $batches, ?Sale $existing = null): array
    {
        $errors = [];
        $oldMap = [];

        if ($existing) {
            foreach ($existing->batches ?? [] as $batch) {
                if (! is_array($batch)) {
                    continue;
                }
                $id = (int) ($batch['emballage_id'] ?? 0);
                $oldMap[$id] = ($oldMap[$id] ?? 0) + (int) ($batch['quantity'] ?? 0);
            }
        }

        $newMap = [];
        foreach ($batches as $index => $batch) {
            if (! is_array($batch)) {
                continue;
            }

            $emballageId = (int) ($batch['emballage_id'] ?? 0);
            $qty = (int) ($batch['quantity'] ?? 0);
            $unitPrice = (float) ($batch['unit_price'] ?? 0);
            $lineTotal = (float) ($batch['line_total'] ?? 0);
            $expectedTotal = round($qty * $unitPrice, 2);

            if (abs($lineTotal - $expectedTotal) > 0.01) {
                $errors["batches.{$index}.line_total"] = 'Line total must equal quantity × unit price.';
            }

            $newMap[$emballageId] = ($newMap[$emballageId] ?? 0) + $qty;
        }

        foreach ($newMap as $emballageId => $newQty) {
            $emb = Emballage::query()->find($emballageId);
            if (! $emb) {
                $errors['batches'] = 'One or more packaging batches do not exist.';

                continue;
            }

            $available = (int) $emb->item + (int) ($oldMap[$emballageId] ?? 0);
            if ($newQty > $available) {
                $errors['batches'] = "Not enough stock in packaging batch {$emb->batch}. Available: {$available} units.";

                break;
            }
        }

        return $errors;
    }

    public static function saleReturned(float $returned, int $totalSold): ?string
    {
        if ($returned > $totalSold) {
            return 'Returned units cannot exceed total quantity sold.';
        }

        return null;
    }

    public static function sortingQuantity(float $gross, RawMaterialStock $stock, ?Sorting $existing = null): ?string
    {
        $available = $stock->remainingQuantity();
        if ($existing && (int) $existing->raw_material_stock_id === (int) $stock->id) {
            $available += (float) $existing->quantity_in;
        }

        if ($gross > $available) {
            return sprintf('Not enough stock available. Maximum: %s kg.', number_format($available, 2));
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, float>
     */
    private static function millingItemMap(array $items): array
    {
        $map = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $type = (string) ($item['type'] ?? '');
            $stockId = (int) ($item['stock_id'] ?? 0);
            if ($type === '' || $stockId === 0) {
                continue;
            }
            $key = $type.':'.$stockId;
            $map[$key] = ($map[$key] ?? 0) + (float) ($item['quantity'] ?? 0);
        }

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, string>
     */
    public static function millingItemReferences(array $items): array
    {
        $errors = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }
            $type = (string) ($item['type'] ?? '');
            $stockId = (int) ($item['stock_id'] ?? 0);
            if ($type === '' || $stockId === 0) {
                continue;
            }

            if (! self::resolveMillingBatch($type, $stockId)) {
                $errors["items.{$index}.stock_id"] = 'Selected batch does not exist for this ingredient.';
            }
        }

        return $errors;
    }

    private static function resolveMillingBatch(string $type, int $stockId): Roasting|Sorting|null
    {
        if (in_array($type, ['soy', 'maize'], true)) {
            return Roasting::query()->find($stockId);
        }

        return Sorting::query()->find($stockId);
    }
}
