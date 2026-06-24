<?php

namespace App\Http\Requests\Admin\Milling;

use App\Support\Inventory\FormRequestStockValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);
            $existing = $this->route('milling');
            $oldItems = $existing ? ($existing->items ?? []) : [];

            foreach (FormRequestStockValidator::millingItemReferences($items) as $field => $message) {
                $validator->errors()->add($field, $message);
            }

            $lossMessage = FormRequestStockValidator::millingLoss((float) $this->input('loss', 0), $items);
            if ($lossMessage) {
                $validator->errors()->add('loss', $lossMessage);
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            foreach (FormRequestStockValidator::millingItems($items, $oldItems) as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
    }
}
