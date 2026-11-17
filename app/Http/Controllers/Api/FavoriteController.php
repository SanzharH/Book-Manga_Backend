<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    /**
     * Избранное текущего пользователя: только опубликованные товары, последние добавленные — первыми.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $request->user()
            ->favoriteProducts()
            ->where('is_published', true)
            ->with(['category', 'tags'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('favorites.created_at')
            ->orderByDesc('favorites.id')
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * Добавить товар в избранное. Повторное добавление ничего не меняет.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->is_published, 404);

        $request->user()->favoriteProducts()->syncWithoutDetaching([$product->id]);

        return response()->json(null, 204);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $request->user()->favoriteProducts()->detach($product->id);

        return response()->json(null, 204);
    }
}
