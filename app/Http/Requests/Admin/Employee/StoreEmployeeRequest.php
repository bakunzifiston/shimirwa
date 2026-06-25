<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'       => ['required', 'string', 'max:255'],
            'national_id'     => ['required', 'string', 'max:255', Rule::unique('employees', 'national_id')->ignore($this->route('employee'))],
            'phone_number'    => ['required', 'string', 'max:255'],
            'gender'          => ['required', 'string', 'max:255'],
            'province'        => ['required', 'string', 'max:255'],
            'district'        => ['required', 'string', 'max:255'],
            'position'        => ['required', 'string', 'max:255'],
            'start_date'      => ['required', 'date'],
            'specialties'     => ['nullable', 'string', 'max:255'],
            'specialties_check' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // The hidden `specialties` field is synced by JS; fall back to checkboxes
        // if JS was disabled or the hidden field wasn't populated.
        if (empty($this->specialties) && $this->has('specialties_check')) {
            $this->merge(['specialties' => implode(',', (array) $this->specialties_check)]);
        }
    }
}
