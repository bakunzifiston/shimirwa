<?php

namespace App\Http\Requests\Admin\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_status' => ['required', Rule::in(array_keys(Order::paymentStatuses()))],
            'order_status' => ['required', Rule::in(array_keys(Order::orderStatuses()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
