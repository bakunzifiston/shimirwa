<?php

namespace App\Http\Requests\Admin\RawMaterialStock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRawMaterialStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'client_id' => ['required', 'exists:clients,id'],
            'type' => ['required', Rule::in(array_keys(config('raw_material_stock.types')))],
            'item' => ['required', 'string', 'max:255'],
            'received' => ['required', 'numeric', 'min:0'],
            'rejected' => ['required', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'exists:employees,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $received = (float) $this->input('received', 0);
        $rejected = (float) $this->input('rejected', 0);

        $this->merge([
            'quantity_in' => max($received - $rejected, 0),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((float) $this->input('rejected', 0) > (float) $this->input('received', 0)) {
                $validator->errors()->add('rejected', 'Rejected quantity cannot exceed received quantity.');
            }
        });
    }
}
