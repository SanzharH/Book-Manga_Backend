<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Частичное обновление: проверяются только переданные поля.
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : null;

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'sku' => [
                'sometimes', 'required', 'string', 'max:64',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'format' => ['sometimes', 'required', Rule::in(['book', 'manga'])],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'image_path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}
