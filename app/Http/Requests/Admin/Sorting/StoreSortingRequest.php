<?php

namespace App\Http\Requests\Admin\Sorting;

use App\Models\RawMaterialStock;
use App\Support\Inventory\FormRequestStockValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSortingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'                                => ['required', 'date'],
            'quantity_in'                         => ['required', 'numeric', 'min:0.01'],
            'loss'                                => ['required', 'numeric', 'min:0'],
            'employee_id'                         => ['required', 'exists:employees,id'],
            'allocations'                         => ['required', 'array', 'min:1'],
            'allocations.*.raw_material_stock_id' => ['required', 'exists:raw_material_stocks,id'],
            'allocations.*.quantity_in'           => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ((float) $this->input('loss', 0) > (float) $this->input('quantity_in', 0)) {
                $validator->errors()->add('loss', 'Loss cannot exceed quantity.');
            }

            $stockId = (int) $this->input('raw_material_stock_id');
            if (! $stockId || $validator->errors()->has('raw_material_stock_id')) {
                return;
            }

            $stock = RawMaterialStock::query()->find($stockId);
            if (! $stock || $stock->type !== 'Raw Material') {
                $validator->errors()->add('raw_material_stock_id', 'Select a valid raw material batch.');

                return;
            }

            $allowedDepletedId = $this->allowedDepletedBatchId();
            if (! $stock->hasAvailableStock() && $stockId !== $allowedDepletedId) {
                $validator->errors()->add('raw_material_stock_id', 'Selected batch has no available stock.');

                return;
            }

            $existing = $this->route('sorting');
            $quantityMessage = FormRequestStockValidator::sortingQuantity(
                (float) $this->input('quantity_in', 0),
                $stock,
                $existing
            );
            if ($quantityMessage) {
                $validator->errors()->add('quantity_in', $quantityMessage);
            }
        });
    }

    public function messages(): array
    {
        return [
            'allocations.required' => 'Please select a batch and enter a quantity.',
            'allocations.min'      => 'Please select a batch and enter a quantity.',
        ];
    }
}
