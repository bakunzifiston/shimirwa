<?php

namespace App\Http\Requests\Admin\RawMaterialStock;

use App\Models\PackagingCatalog;
use App\Models\ProductCatalog;
use App\Models\RawMaterialStock;
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
            'batch_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique(RawMaterialStock::class, 'batch_number')
                    ->where(fn ($query) => $query->where('type', $this->input('type'))),
            ],
            'employee_id' => ['required', 'exists:employees,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $received = (float) $this->input('received', 0);
        $rejected = (float) $this->input('rejected', 0);

        $this->merge([
            'quantity_in' => max($received - $rejected, 0),
            'item'        => $this->canonicalizeItemName($this->input('item')),
        ]);
    }

    /**
     * Trim the item name and snap it to the exact casing used in the product/packaging
     * catalog when it matches case-insensitively (e.g. "sugar" or "Sugar " -> "SUGAR").
     * Without this, a free-text "Other" entry that merely differs in case/whitespace
     * from the catalog name silently breaks batch lookups elsewhere (e.g. the Milling
     * ingredient picker matches item names with an exact, case-sensitive comparison).
     */
    private function canonicalizeItemName(?string $item): ?string
    {
        $item = is_string($item) ? trim($item) : $item;
        if (!$item) {
            return $item;
        }

        $catalogNames = ProductCatalog::pluck('name')
            ->merge(PackagingCatalog::pluck('name'));

        $match = $catalogNames->first(fn ($name) => strcasecmp(trim($name), $item) === 0);

        return $match ?? $item;
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
