<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s\-()]{6,32}$/'],
            'delivery_method' => ['sometimes', Rule::in(Order::DELIVERY_METHODS)],
            'payment_method' => ['sometimes', Rule::in(Order::PAYMENT_METHODS)],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Укажите телефон в формате +7 700 000 00 00.',
        ];
    }
}
