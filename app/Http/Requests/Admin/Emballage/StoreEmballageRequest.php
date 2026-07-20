<?php

namespace App\Http\Requests\Admin\Emballage;

use App\Models\Milling;
use App\Models\PackagingCatalog;
use App\Models\RawMaterialStock;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmballageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'                        => ['required', 'date'],
            'packaging_batch_id'          => ['required', 'string', 'max:255'],
            'packaging_catalog_id'        => ['required', 'exists:packaging_catalogs,id'],
            'raw_material_stock_id'       => ['nullable', 'exists:raw_material_stocks,id'],
            'packaging_overflow'          => ['sometimes', 'array'],
            'packaging_overflow.*.stock_id' => ['required_with:packaging_overflow', 'exists:raw_material_stocks,id'],
            'packaging_overflow.*.units'    => ['required_with:packaging_overflow', 'numeric', 'min:1'],
            'milling_id'                  => ['required', 'exists:millings,id'],
            'milling_overflow'            => ['sometimes', 'array'],
            'milling_overflow.*.milling_id' => ['required_with:milling_overflow', 'exists:millings,id'],
            'milling_overflow.*.quantity'   => ['required_with:milling_overflow', 'numeric', 'min:0.001'],
            'inner_stock_id'              => ['nullable', 'exists:raw_material_stocks,id'],
            'item'                        => ['required', 'numeric', 'min:1'],
            'quantity'                    => ['required', 'numeric', 'min:0'],
            'damaged'                     => ['nullable', 'numeric', 'min:0'],
            'unit_price'                  => ['nullable', 'numeric', 'min:0'],
            'total_price'                 => ['nullable', 'numeric', 'min:0'],
            'expiry_date'                 => ['nullable', 'date'],
            'comment'                     => ['nullable', 'string', 'max:500'],
            'employee_id'                 => ['required', 'exists:employees,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $millingId  = $this->input('milling_id');
            $overflow   = array_filter($this->input('milling_overflow', []));
            $ovKg       = (float) array_sum(array_column($overflow, 'quantity'));
            // quantity is the TOTAL; the primary batch supplies total minus overflow
            $primaryQty = max((float) $this->input('quantity', 0) - $ovKg, 0);

            // What this record (on edit) already holds per milling batch — add back before checking
            $emballage = $this->route('emballage');
            $heldFlour = [];
            if ($emballage) {
                if ($emballage->milling_id) {
                    $heldFlour[$emballage->milling_id] = ($heldFlour[$emballage->milling_id] ?? 0) + $emballage->primaryFlourKg();
                }
                foreach ($emballage->milling_overflow ?? [] as $prev) {
                    if (!empty($prev['milling_id'])) {
                        $heldFlour[$prev['milling_id']] = ($heldFlour[$prev['milling_id']] ?? 0) + (float) ($prev['quantity'] ?? 0);
                    }
                }
            }

            // Validate primary batch availability
            if ($millingId && $primaryQty > 0) {
                $milling = Milling::find($millingId);
                if ($milling) {
                    $available = (float) $milling->output_flour + ($heldFlour[$milling->id] ?? 0);
                    if ($primaryQty > $available) {
                        $validator->errors()->add('milling_id',
                            sprintf('Milling batch %s only has %s kg available (needs %s kg).',
                                $milling->batch_number, number_format($available, 2), number_format($primaryQty, 2))
                        );
                    }
                }
            }

            // Validate inner unit stock availability
            $catalogId    = $this->input('packaging_catalog_id');
            $innerStockId = $this->input('inner_stock_id');
            if ($catalogId && $innerStockId) {
                $catalog = PackagingCatalog::find($catalogId);
                if ($catalog && $catalog->hasInnerUnits()) {
                    $itemCount   = (int) $this->input('item', 0);
                    $totalInner  = $itemCount * $catalog->inner_units_per_package;
                    $innerStock  = RawMaterialStock::find($innerStockId);
                    $emballage   = $this->route('emballage');
                    $avail       = $innerStock ? (float) $innerStock->quantity_in : 0;
                    if ($emballage && $emballage->inner_stock_id == $innerStockId) {
                        $avail += $emballage->innerUnitsTotal();
                    }
                    if ($totalInner > $avail) {
                        $validator->errors()->add('inner_stock_id',
                            sprintf('Batch %s only has %s units; need %s for %d × %s.',
                                $innerStock->batch_number ?? '?',
                                number_format($avail),
                                number_format($totalInner),
                                $itemCount,
                                $catalog->name
                            )
                        );
                    }
                }
            }

            // Validate packaging-material batch availability (primary + overflow)
            $stockId     = $this->input('raw_material_stock_id');
            $pkgOverflow = array_filter($this->input('packaging_overflow', []));
            $ovUnits     = (float) array_sum(array_column($pkgOverflow, 'units'));
            $primaryUnits = max((float) $this->input('item', 0) - $ovUnits, 0);
            $emballage   = $this->route('emballage');

            if ($stockId && $primaryUnits > 0) {
                $stock = RawMaterialStock::find($stockId);
                if ($stock) {
                    $avail = (float) $stock->quantity_in;
                    // On edit, add back what this record already consumed from this batch
                    if ($emballage && $emballage->raw_material_stock_id == $stockId) {
                        $avail += $emballage->primaryPackagingUnits();
                    }
                    foreach ($emballage?->packaging_overflow ?? [] as $prev) {
                        if (($prev['stock_id'] ?? null) == $stockId) {
                            $avail += (float) ($prev['units'] ?? 0);
                        }
                    }
                    if ($primaryUnits > $avail) {
                        $validator->errors()->add('raw_material_stock_id',
                            sprintf('Packaging batch %s only has %s units (needs %s).',
                                $stock->batch_number, number_format($avail), number_format($primaryUnits))
                        );
                    }
                }
            }

            foreach ($pkgOverflow as $j => $ov) {
                $ovStockId = $ov['stock_id'] ?? null;
                $ovQty     = (float) ($ov['units'] ?? 0);
                if (!$ovStockId || $ovQty <= 0) continue;

                $stock = RawMaterialStock::find($ovStockId);
                if (!$stock) continue;

                $avail = (float) $stock->quantity_in;
                if ($emballage && $emballage->raw_material_stock_id == $ovStockId) {
                    $avail += $emballage->primaryPackagingUnits();
                }
                foreach ($emballage?->packaging_overflow ?? [] as $prev) {
                    if (($prev['stock_id'] ?? null) == $ovStockId) {
                        $avail += (float) ($prev['units'] ?? 0);
                    }
                }
                if ($ovQty > $avail) {
                    $validator->errors()->add("packaging_overflow.{$j}.units",
                        sprintf('Packaging batch %s only has %s units available.',
                            $stock->batch_number, number_format($avail))
                    );
                }
            }

            // Validate each flour overflow batch
            foreach ($overflow as $j => $ov) {
                $ovMillingId = $ov['milling_id'] ?? null;
                $ovQty       = (float) ($ov['quantity'] ?? 0);
                if (!$ovMillingId || $ovQty <= 0) continue;

                $m = Milling::find($ovMillingId);
                if (!$m) continue;

                $avail = (float) $m->output_flour + ($heldFlour[$m->id] ?? 0);
                if ($ovQty > $avail) {
                    $validator->errors()->add("milling_overflow.{$j}.quantity",
                        sprintf('Batch %s only has %s kg available.', $m->batch_number, number_format($avail, 2))
                    );
                }
            }
        });
    }
}
