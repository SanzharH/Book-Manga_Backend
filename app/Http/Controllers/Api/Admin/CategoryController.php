<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(
            Category::query()->orderBy('name')->paginate(15)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create($data);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category): CategoryResource
    {
        $data = $request->validate([
            'name' => [
                'sometimes', 'required', 'string', 'max:120',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'slug' => [
                'sometimes', 'required', 'string', 'max:140', 'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $category->update($data);

        return new CategoryResource($category->refresh());
    }

    public function destroy(Category $category): JsonResponse
    {
        $hasProducts = Product::query()
            ->where('category_id', $category->id)
            ->exists();

        if ($hasProducts) {
            return response()->json([
                'message' => 'Нельзя удалить категорию, пока в ней есть товары.',
            ], 409);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
