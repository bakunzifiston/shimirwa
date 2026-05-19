<?php

namespace App\Http\Requests\Admin\Client;

use App\Models\Client;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends StoreClientRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['supplier_code'] = [
            'nullable',
            'string',
            'max:255',
            Rule::unique(Client::class, 'supplier_code')->ignore($this->route('client')),
            Rule::requiredIf($this->input('role') === 'supplier'),
        ];

        return $rules;
    }
}
