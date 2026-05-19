<?php

namespace App\Http\Requests\Admin\Roasting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoastingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'source_type' => ['required', Rule::in(['raw', 'sorting'])],
            'raw_material_stock_id' => ['nullable', 'required_if:source_type,raw', 'exists:raw_material_stocks,id'],
            'sorting_id' => ['nullable', 'required_if:source_type,sorting', 'exists:sortings,id'],
            'quantity_in' => ['required', 'numeric', 'min:0.01'],
            'loss' => ['required', 'numeric', 'min:0'],
            'batch' => ['required', 'string', 'max:255'],
            'chef_id' => ['required', 'exists:employees,id'],
            'supervisor_id' => ['required', 'exists:employees,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((float) $this->input('loss', 0) > (float) $this->input('quantity_in', 0)) {
                $validator->errors()->add('loss', 'Loss cannot exceed quantity in.');
            }
        });
    }
}
