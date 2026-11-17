<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
        ];
    }
}
