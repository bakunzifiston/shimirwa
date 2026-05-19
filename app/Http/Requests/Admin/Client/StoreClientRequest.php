<?php

namespace App\Http\Requests\Admin\Client;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'client_type' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['client', 'supplier'])],
            'supplier_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(Client::class, 'supplier_code'),
                Rule::requiredIf($this->input('role') === 'supplier'),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
        ];
    }
}
