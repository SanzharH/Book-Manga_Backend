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
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_published', true);

        if (! empty($filters['search'])) {
        $search = $filters['search'];

        $query->where(function ($query) use ($search): void {
        $query->where('title', 'like', "%{$search}%")
            ->orWhere('author', 'like', "%{$search}%");
        });
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

        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';

        if ($sort === 'rating') {
            $query->orderByRaw('reviews_avg_rating IS NULL ASC')
            ->orderBy('reviews_avg_rating', $direction);
        } elseif ($sort === 'popular') {
            $query->orderBy('reviews_count', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        return ProductResource::collection(
            $query->paginate($filters['per_page'] ?? 15)
        );
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_published, 404);

        $product->load(['category', 'tags']);
        $product->loadAvg('reviews', 'rating')
        ->loadCount('reviews');

        return new ProductResource($product);
    }
}
