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
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->exists('shipping_name') && ! $this->exists('shipping_address')) {
                $validator->errors()->add(
                    'order',
                    'Укажи новое имя получателя или адрес доставки.',
                );
            }
        });
    }
}