<?php

namespace App\Http\Requests\Admin\Roasting;

use App\Models\RawMaterialStock;
use App\Models\Sorting;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoastingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'                       => ['required', 'date'],
            'quantity_in'                => ['required', 'numeric', 'min:0.01'],
            'loss'                       => ['required', 'numeric', 'min:0'],
            'batch'                      => ['required', 'string', 'max:255'],
            'chef_id'                    => ['required', 'exists:employees,id'],
            'supervisor_id'              => ['required', 'exists:employees,id'],
            'allocations'                => ['sometimes', 'array', 'min:1'],
            'allocations.*.source_batch' => ['required_with:allocations', 'string'],
            'allocations.*.quantity_in'  => ['required_with:allocations', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $totalQty    = (float) $this->input('quantity_in', 0);
            $loss        = (float) $this->input('loss', 0);
            $allocations = $this->input('allocations', []);

            if ($loss > $totalQty) {
                $validator->errors()->add('loss', 'Loss cannot exceed quantity in.');
            }

            // If no allocations submitted, derive one from the hint field for validation
            if (empty($allocations)) {
                $hint = $this->input('_source_batch_hint', '');
                if ($hint) {
                    $allocations = [['source_batch' => $hint, 'quantity_in' => $totalQty]];
                } else {
                    $validator->errors()->add('source_batch', 'Please select a source batch.');
                    return;
                }
            }

            foreach ($allocations as $i => $alloc) {
                $key = $alloc['source_batch'] ?? '';
                $qty = (float) ($alloc['quantity_in'] ?? 0);
                [$type, $id] = array_pad(explode(':', $key, 2), 2, null);

                if ($type === 'raw') {
                    $stock = RawMaterialStock::find($id);
                    if (!$stock) {
                        $validator->errors()->add("allocations.{$i}.source_batch", 'Batch not found.');
                    } elseif ($qty > (float) $stock->quantity_in) {
                        $validator->errors()->add("allocations.{$i}.quantity_in",
                            'Exceeds available: ' . number_format($stock->quantity_in, 2) . ' kg.');
                    }
                } elseif ($type === 'sorting') {
                    $sorting = Sorting::find($id);
                    if (!$sorting) {
                        $validator->errors()->add("allocations.{$i}.source_batch", 'Sorting batch not found.');
                    } elseif ($qty > (float) $sorting->quantity_out) {
                        $validator->errors()->add("allocations.{$i}.quantity_in",
                            'Exceeds available from sorting: ' . number_format($sorting->quantity_out, 2) . ' kg.');
                    }
                } else {
                    $validator->errors()->add("allocations.{$i}.source_batch", 'Invalid source batch.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'allocations.min' => 'Please select a source batch and enter a quantity.',
        ];
    }
}
