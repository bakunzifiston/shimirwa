<?php

namespace App\Http\Requests\Admin\Employee;

use App\Models\Employee;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends StoreEmployeeRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['national_id'] = [
            'required',
            'string',
            'max:255',
            Rule::unique(Employee::class, 'national_id')->ignore($this->route('employee')),
        ];

        return $rules;
    }
}
