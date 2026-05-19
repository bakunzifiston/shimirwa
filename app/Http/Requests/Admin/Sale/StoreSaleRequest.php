<?php

namespace App\Http\Requests\Admin\Sale;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'item' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'exists:clients,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'returned' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'batches' => ['required', 'array', 'min:1'],
            'batches.*.emballage_id' => ['required', 'exists:emballages,id'],
            'batches.*.quantity' => ['required', 'integer', 'min:1'],
            'batches.*.unit_price' => ['required', 'numeric', 'min:0'],
            'batches.*.line_total' => ['required', 'numeric', 'min:0'],
        ];
    }
}
