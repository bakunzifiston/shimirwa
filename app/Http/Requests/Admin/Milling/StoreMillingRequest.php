<?php

namespace App\Http\Requests\Admin\Milling;

use Illuminate\Foundation\Http\FormRequest;

class StoreMillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'batch_number' => ['required', 'string', 'max:255'],
            'loss' => ['required', 'numeric', 'min:0'],
            'employee_id' => ['required', 'exists:employees,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'in:soy,sorghum,wheat,maize'],
            'items.*.stock_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
