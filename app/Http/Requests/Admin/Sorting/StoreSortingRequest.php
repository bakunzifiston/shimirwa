<?php

namespace App\Http\Requests\Admin\Sorting;

use Illuminate\Foundation\Http\FormRequest;

class StoreSortingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'raw_material_stock_id' => ['required', 'exists:raw_material_stocks,id'],
            'quantity_in' => ['required', 'numeric', 'min:0.01'],
            'loss' => ['required', 'numeric', 'min:0'],
            'employee_id' => ['required', 'exists:employees,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((float) $this->input('loss', 0) > (float) $this->input('quantity_in', 0)) {
                $validator->errors()->add('loss', 'Loss cannot exceed quantity.');
            }
        });
    }
}
