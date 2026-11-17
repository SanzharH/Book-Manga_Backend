<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && ($this->user()?->can('update', $order) ?? false);
    }

    public function rules(): array
    {
        return [
            'shipping_name' => ['sometimes', 'required', 'string', 'max:255'],
            'shipping_address' => ['sometimes', 'required', 'string', 'max:2000'],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^\+?[0-9\s\-()]{6,32}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Укажите телефон в формате +7 700 000 00 00.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['shipping_name', 'shipping_address', 'phone'])) {
                $validator->errors()->add(
                    'order',
                    'Укажите новое имя получателя, адрес или телефон.',
                );
            }
        });
    }
}
