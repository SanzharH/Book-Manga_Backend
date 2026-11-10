<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_published_product_reviews(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
        ]);

        $this->getJson("/api/products/{$product->id}/reviews")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $review->id)
            ->assertJsonPath('data.0.rating', 5);
    }

    public function test_authenticated_buyer_can_create_review(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($buyer);

        $this->postJson("/api/products/{$product->id}/reviews", [
            'rating' => 5,
            'body' => 'Отличное издание.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.body', 'Отличное издание.');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'body' => 'Отличное издание.',
        ]);
    }

    public function test_buyer_cannot_review_same_product_twice(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();

        Review::factory()->create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($buyer);

        $this->postJson("/api/products/{$product->id}/reviews", [
            'rating' => 4,
            'body' => 'Повторный отзыв.',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product');
    }

    public function test_guest_cannot_create_review(): void
    {
        $product = Product::factory()->create();

        $this->postJson("/api/products/{$product->id}/reviews", [
            'rating' => 5,
            'body' => 'Отзыв без входа.',
        ])->assertUnauthorized();
    }

    public function test_review_rating_must_be_between_one_and_five(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $product = Product::factory()->create();

        $this->postJson("/api/products/{$product->id}/reviews", [
            'rating' => 6,
            'body' => 'Недопустимая оценка.',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');
    }
}
