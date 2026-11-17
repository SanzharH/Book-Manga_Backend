<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class RecommendProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'preferences' => ['required', 'string', 'min:3', 'max:500'],
            'format' => ['sometimes', 'nullable', 'in:book,manga'],
            'max_price' => ['sometimes', 'nullable', 'numeric', 'min:0.01'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ];
    }
}
