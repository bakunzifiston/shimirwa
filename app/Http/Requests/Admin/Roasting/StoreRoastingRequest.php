<?php

namespace App\Http\Requests\Admin\Roasting;

use App\Support\Inventory\FormRequestStockValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ((float) $this->input('loss', 0) > (float) $this->input('quantity_in', 0)) {
                $validator->errors()->add('loss', 'Loss cannot exceed quantity in.');
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $existing = $this->route('roasting');
            foreach (FormRequestStockValidator::roasting($this->all(), $existing) as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
    }

    /**
     * Database-safe attributes only. Excludes UI-only fields such as source_type.
     *
     * @return array<string, mixed>
     */
    public function persistedAttributes(): array
    {
        $validated = $this->validated();
        $source = $validated['source_type'] ?? 'raw';

        return [
            'date' => $validated['date'],
            'quantity_in' => $validated['quantity_in'],
            'loss' => $validated['loss'],
            'batch' => $validated['batch'],
            'chef_id' => $validated['chef_id'],
            'supervisor_id' => $validated['supervisor_id'],
            'raw_material_stock_id' => $source === 'raw' ? $validated['raw_material_stock_id'] : null,
            'sorting_id' => $source === 'sorting' ? $validated['sorting_id'] : null,
        ];
    }
}
