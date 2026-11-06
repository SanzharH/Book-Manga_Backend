<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index(Product $product): AnonymousResourceCollection
    {
        abort_unless($product->is_published, 404);

        $reviews = $product->reviews()
            ->with('user')
            ->latest()
            ->paginate(15);

        return ReviewResource::collection($reviews);
    }

    public function store(
        StoreReviewRequest $request,
        Product $product,
    ): JsonResponse {
        abort_unless($product->is_published, 404);

        $user = $request->user();

        if ($product->reviews()->where('user_id', $user->getKey())->exists()) {
            throw ValidationException::withMessages([
                'product' => ['Вы уже оставили отзыв на этот товар.'],
            ]);
        }

        $review = $product->reviews()->make($request->validated());
        $review->user()->associate($user);
        $review->save();
        $review->load('user');

        return (new ReviewResource($review))
            ->response()
            ->setStatusCode(201);
    }
}