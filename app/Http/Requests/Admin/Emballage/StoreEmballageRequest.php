<?php

namespace App\Http\Requests\Admin\Emballage;

use App\Models\Milling;
use App\Models\PackagingCatalog;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmballageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Split total quantity into primary + overflow based on submitted overflow draws
        $overflow   = $this->input('milling_overflow', []);
        $totalQty   = (float) $this->input('quantity', 0);
        $ovTotal    = array_sum(array_column(array_filter($overflow), 'quantity'));
        $primaryQty = max(round($totalQty - $ovTotal, 4), 0);

        $this->merge([
            'quantity'      => $primaryQty,          // primary batch gets its portion
            'quantity_total' => $totalQty,            // keep full total for display/hints
        ]);
    }

    public function rules(): array
    {
        return [
            'date'                        => ['required', 'date'],
            'packaging_batch_id'          => ['required', 'string', 'max:255'],
            'packaging_catalog_id'        => ['required', 'exists:packaging_catalogs,id'],
            'raw_material_stock_id'       => ['nullable', 'exists:raw_material_stocks,id'],
            'milling_id'                  => ['required', 'exists:millings,id'],
            'milling_overflow'            => ['sometimes', 'array'],
            'milling_overflow.*.milling_id' => ['required_with:milling_overflow', 'exists:millings,id'],
            'milling_overflow.*.quantity'   => ['required_with:milling_overflow', 'numeric', 'min:0.001'],
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
            $millingId = $this->input('milling_id');
            $primaryQty = (float) $this->input('quantity', 0);
            $overflow   = $this->input('milling_overflow', []);

            // Validate primary batch availability
            if ($millingId && $primaryQty > 0) {
                $milling = Milling::find($millingId);
                if ($milling) {
                    $available = (float) $milling->output_flour;
                    $emballage = $this->route('emballage');
                    if ($emballage && $emballage->milling_id == $millingId) {
                        $available += (float) $emballage->quantity;
                    }
                    if ($primaryQty > $available) {
                        $validator->errors()->add('milling_id',
                            sprintf('Milling batch %s only has %s kg available (needs %s kg).',
                                $milling->batch_number, number_format($available, 2), number_format($primaryQty, 2))
                        );
                    }
                }
            }

            // Validate each overflow batch
            foreach ($overflow as $j => $ov) {
                $ovMillingId = $ov['milling_id'] ?? null;
                $ovQty       = (float) ($ov['quantity'] ?? 0);
                if (!$ovMillingId || $ovQty <= 0) continue;

                $m = Milling::find($ovMillingId);
                if (!$m) continue;

                $avail = (float) $m->output_flour;
                if ($ovQty > $avail) {
                    $validator->errors()->add("milling_overflow.{$j}.quantity",
                        sprintf('Batch %s only has %s kg available.', $m->batch_number, number_format($avail, 2))
                    );
                }
            }
        });
    }
}
