<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorites_require_authentication(): void
    {
        $product = Product::factory()->create();

        $this->getJson('/api/favorites')->assertUnauthorized();
        $this->putJson("/api/favorites/{$product->id}")->assertUnauthorized();
        $this->deleteJson("/api/favorites/{$product->id}")->assertUnauthorized();
    }

    public function test_user_adds_and_removes_favorites(): void
    {
        $user = User::factory()->create();
        [$first, $second] = Product::factory()->count(2)->create()->all();
        Sanctum::actingAs($user);

        $this->putJson("/api/favorites/{$first->id}")->assertNoContent();
        $this->putJson("/api/favorites/{$second->id}")->assertNoContent();
        // Повторное добавление не создаёт дубликат
        $this->putJson("/api/favorites/{$first->id}")->assertNoContent();

        $this->getJson('/api/favorites')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'title', 'price', 'category']]]);

        $this->deleteJson("/api/favorites/{$first->id}")->assertNoContent();

        $this->getJson('/api/favorites')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $second->id);
    }

    public function test_favorites_are_not_shared_between_users(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($alice);
        $this->putJson("/api/favorites/{$product->id}")->assertNoContent();

        Sanctum::actingAs($bob);
        $this->getJson('/api/favorites')->assertOk()->assertJsonCount(0, 'data');

        Sanctum::actingAs($alice);
        $this->getJson('/api/favorites')->assertJsonCount(1, 'data');
    }

    public function test_hidden_products_cannot_be_added_and_are_not_listed(): void
    {
        $user = User::factory()->create();
        $hidden = Product::factory()->create(['is_published' => false]);
        $product = Product::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson("/api/favorites/{$hidden->id}")->assertNotFound();

        $this->putJson("/api/favorites/{$product->id}")->assertNoContent();
        $product->update(['is_published' => false]);

        $this->getJson('/api/favorites')->assertJsonCount(0, 'data');
    }
}
