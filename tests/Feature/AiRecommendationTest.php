<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiRecommendationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'gemini-test-model',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
        ]);

        Cache::flush();
    }

    public function test_buyer_can_get_product_recommendations(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $product = Product::factory()->create([
            'is_published' => true,
            'stock' => 5,
            'format' => 'manga',
        ]);

        Sanctum::actingAs($user);

        Http::fake([
            '*generateContent' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'summary' => 'Подборка по вашим предпочтениям.',
                                'recommendations' => [[
                                    'product_id' => $product->id,
                                    'reason' => 'Подходит по жанру и формату.',
                                ]],
                            ], JSON_UNESCAPED_UNICODE),
                        ]],
                    ],
                ]],
                'usageMetadata' => [
                    'promptTokenCount' => 20,
                    'candidatesTokenCount' => 15,
                    'totalTokenCount' => 35,
                ],
            ], 200),
        ]);

        $this->postJson('/api/ai/recommendations', [
            'preferences' => 'Фэнтези и приключения',
            'format' => 'manga',
            'limit' => 3,
        ])
            ->assertOk()
            ->assertJsonPath('data.summary', 'Подборка по вашим предпочтениям.')
            ->assertJsonPath('data.recommendations.0.product.id', $product->id)
            ->assertJsonPath('data.recommendations.0.reason', 'Подходит по жанру и формату.')
            ->assertJsonPath('meta.cached', false);

        Http::assertSent(fn ($request) =>
            str_ends_with($request->url(), 'gemini-test-model:generateContent')
            && $request->hasHeader('x-goog-api-key', 'test-api-key')
        );
    }

    public function test_recommendations_validation_errors_are_returned(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/ai/recommendations', [
            'preferences' => 'x',
            'format' => 'invalid',
            'limit' => 10,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['preferences', 'format', 'limit']);

        Http::assertNothingSent();
    }

    public function test_empty_catalog_returns_without_calling_gemini(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/ai/recommendations', [
            'preferences' => 'Фэнтези и приключения',
        ])
            ->assertOk()
            ->assertJsonPath('data.summary', 'Нет доступных товаров, подходящих под условия.')
            ->assertJsonCount(0, 'data.recommendations')
            ->assertJsonPath('meta.cached', false);

        Http::assertNothingSent();
    }

    public function test_gemini_unavailable_returns_service_unavailable(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Product::factory()->create([
            'is_published' => true,
            'stock' => 5,
        ]);

        Http::fake([
            '*generateContent' => Http::response([
                'error' => [
                    'code' => 503,
                    'status' => 'UNAVAILABLE',
                    'message' => 'Temporarily unavailable',
                ],
            ], 503),
        ]);

        $this->postJson('/api/ai/recommendations', [
            'preferences' => 'Фэнтези и приключения',
        ])
            ->assertStatus(503)
            ->assertJsonPath('error', 'ai_service_unavailable');
    }

    public function test_guest_cannot_request_recommendations(): void
    {
        $this->postJson('/api/ai/recommendations', [
            'preferences' => 'Фэнтези и приключения',
        ])->assertUnauthorized();

        Http::assertNothingSent();
    }
}