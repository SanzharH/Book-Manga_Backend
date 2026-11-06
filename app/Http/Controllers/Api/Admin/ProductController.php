<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(
            Product::query()->orderByDesc('created_at')->paginate(15)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'format' => ['required', Rule::in(['book', 'manga'])],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $product = Product::create($data);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function update(Request $request, Product $product): ProductResource
    {
        $data = $request->validate([
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'sku' => [
                'sometimes', 'required', 'string', 'max:64',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'format' => ['sometimes', 'required', Rule::in(['book', 'manga'])],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'image_path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $product->update($data);

        return new ProductResource($product->refresh());
    }

    public function destroy(Product $product): JsonResponse
    {
        $hasOrderItems = OrderItem::query()
            ->where('product_id', $product->id)
            ->exists();

        $hasReviews = Review::query()
            ->where('product_id', $product->id)
            ->exists();

        if ($hasOrderItems || $hasReviews) {
            return response()->json([
                'message' => 'Нельзя удалить товар, связанный с заказом или отзывом.',
            ], 409);
        }

        $product->delete();

        return response()->json(null, 204);
    }
}
