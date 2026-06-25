<?php

namespace App\Http\Requests\Admin\Sale;

use App\Support\Inventory\FormRequestStockValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $batches = $this->input('batches', []);
            $existing = $this->route('sale');

            foreach (FormRequestStockValidator::saleBatches($batches, $existing) as $field => $message) {
                $validator->errors()->add($field, $message);
            }

            $totalSold = (int) collect($batches)->sum(fn ($b) => (int) ($b['quantity'] ?? 0));
            $returnedMessage = FormRequestStockValidator::saleReturned((float) $this->input('returned', 0), $totalSold);
            if ($returnedMessage) {
                $validator->errors()->add('returned', $returnedMessage);
            }
        });
    }
}
