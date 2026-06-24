<?php

namespace App\Http\Requests\Admin\RawMaterialStock;

use App\Models\RawMaterialStock;
use Illuminate\Validation\Rule;

class UpdateRawMaterialStockRequest extends StoreRawMaterialStockRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['batch_number'] = [
            'required',
            'string',
            'max:255',
            Rule::unique(RawMaterialStock::class, 'batch_number')
                ->where(fn ($query) => $query->where('type', $this->input('type')))
                ->ignore($this->route('raw_material_stock')),
        ];

        return $rules;
    }
}
