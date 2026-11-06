<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductFilterRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(ProductFilterRequest $request)
    {
        $filters = $request->validated();

        $query = Product::query()
            ->with(['category', 'tags'])
            ->where('is_published', true);

        if (! empty($filters['search'])) {
            $query->whereFullText(
                ['title', 'author'],
                $filters['search']
            );
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['format'])) {
            $query->where('format', $filters['format']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (array_key_exists('in_stock', $filters)) {
            $query->where(
                'stock',
                $filters['in_stock'] ? '>' : '=',
                0
            );
        }

        $query->orderBy(
            $filters['sort'] ?? 'created_at',
            $filters['direction'] ?? 'desc'
        );

        return ProductResource::collection(
            $query->paginate($filters['per_page'] ?? 15)
        );
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_published, 404);

        $product->load(['category', 'tags']);

        return new ProductResource($product);
    }
}
