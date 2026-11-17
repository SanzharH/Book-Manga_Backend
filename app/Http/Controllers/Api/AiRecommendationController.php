<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AiServiceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AI\RecommendProductsRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Services\AiRecommendationService;
use Illuminate\Http\JsonResponse;

class AiRecommendationController extends Controller
{
    public function __invoke(
        RecommendProductsRequest $request,
        AiRecommendationService $service
    ): JsonResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $result = $service->recommend($user, $request->validated());
        } catch (AiServiceException) {
            return response()->json([
                'message' => 'Сервис рекомендаций временно недоступен.',
                'error' => 'ai_service_unavailable',
            ], 503);
        }

        $productIds = collect($result['recommendations'])
            ->pluck('product_id')
            ->all();

        $products = Product::query()
            ->with(['category', 'tags'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $recommendations = collect($result['recommendations'])
            ->map(function (array $recommendation) use ($products, $request): ?array {
                $product = $products->get($recommendation['product_id']);

                if (! $product) {
                    return null;
                }

                return [
                    'product' => (new ProductResource($product))->resolve($request),
                    'reason' => $recommendation['reason'],
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'data' => [
                'summary' => $result['summary'],
                'recommendations' => $recommendations,
            ],
            'meta' => [
                'cached' => $result['cached'],
            ],
        ]);
    }
}
