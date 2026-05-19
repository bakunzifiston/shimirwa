<?php

namespace App\Http\Requests\Admin\Emballage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmballageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'packaging_batch_id' => ['required', 'string', 'max:255'],
            'packaging_type' => ['required', Rule::in(['box', '1kg', '5kg', 'sack'])],
            'raw_material_stock_id' => ['required', 'exists:raw_material_stocks,id'],
            'envelope_stock_id' => ['nullable', 'required_if:packaging_type,box', 'exists:raw_material_stocks,id'],
            'milling_id' => ['nullable', 'exists:millings,id'],
            'item' => ['required', 'numeric', 'min:1'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'damaged' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'comment' => ['nullable', 'string', 'max:500'],
            'employee_id' => ['required', 'exists:employees,id'],
        ];
    }
}
