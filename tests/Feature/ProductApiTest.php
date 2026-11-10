<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_public(): void
    {
        $category = Category::factory()->create();

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $category->id)
            ->assertJsonPath('data.0.name', $category->name);
    }

    public function test_product_list_contains_rating_and_review_count(): void
    {
        $product = Product::factory()->create();
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $firstUser->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $secondUser->id,
            'rating' => 4,
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.rating', 4.5)
            ->assertJsonPath('data.0.reviews_count', 2);
    }

    public function test_catalog_filters_by_format_price_and_stock(): void
    {
        $matchingProduct = Product::factory()->create([
            'format' => 'manga',
            'price' => 450,
            'stock' => 3,
        ]);

        Product::factory()->create([
            'format' => 'book',
            'price' => 450,
            'stock' => 3,
        ]);

        Product::factory()->create([
            'format' => 'manga',
            'price' => 100,
            'stock' => 0,
        ]);

        $this->getJson('/api/products?format=manga&min_price=300&in_stock=true')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingProduct->id);
    }

    public function test_search_finds_partial_product_title(): void
    {
        $product = Product::factory()->create([
            'title' => 'Berserk Deluxe Edition',
        ]);

        Product::factory()->create([
            'title' => 'One Piece Volume One',
        ]);

        $this->getJson('/api/products?search=serk')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id);
    }

    public function test_catalog_sorts_by_rating_and_popularity(): void
    {
        $popularProduct = Product::factory()->create();
        $highRatedProduct = Product::factory()->create();

        $users = User::factory()->count(3)->create();

        Review::factory()->create([
            'product_id' => $popularProduct->id,
            'user_id' => $users[0]->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'product_id' => $popularProduct->id,
            'user_id' => $users[1]->id,
            'rating' => 3,
        ]);

        Review::factory()->create([
            'product_id' => $highRatedProduct->id,
            'user_id' => $users[2]->id,
            'rating' => 5,
        ]);

        $this->getJson('/api/products?sort=rating&direction=desc')
            ->assertOk()
            ->assertJsonPath('data.0.id', $highRatedProduct->id);

        $this->getJson('/api/products?sort=popular&direction=desc')
            ->assertOk()
            ->assertJsonPath('data.0.id', $popularProduct->id);
    }
}