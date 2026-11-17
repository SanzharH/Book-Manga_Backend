<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->with(['category', 'tags'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15)
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated())->load(['category', 'tags']);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'tags']));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product->refresh()->load(['category', 'tags']));
    }

    public function destroy(Product $product): JsonResponse
    {
        // Товар из заказов и отзывов не удаляем, чтобы не терять историю; его можно скрыть
        if ($product->orderItems()->exists() || $product->reviews()->exists()) {
            return response()->json([
                'message' => 'Нельзя удалить товар, связанный с заказом или отзывом. Снимите его с публикации.',
            ], 409);
        }

        $product->delete();

        return response()->json(null, 204);
    }
}
