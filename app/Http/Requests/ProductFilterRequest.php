<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'format' => ['sometimes', 'in:book,manga'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
            'in_stock' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'in:title,price,created_at'],
            'direction' => ['sometimes', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $min = $this->input('min_price');
            $max = $this->input('max_price');

            if (
                is_numeric($min)
                && is_numeric($max)
                && (float) $max < (float) $min
            ) {
                $validator->errors()->add(
                    'max_price',
                    'Максимальная цена должна быть не меньше минимальной.'
                );
            }
        });
    }
}