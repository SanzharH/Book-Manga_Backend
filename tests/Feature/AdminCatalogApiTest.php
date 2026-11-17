<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_admin_creates_updates_and_deletes_product(): void
    {
        $category = Category::factory()->create();

        $id = $this->postJson('/api/admin/products', [
            'category_id' => $category->id,
            'sku' => 'TEST-001',
            'title' => 'Тестовая манга',
            'author' => 'Автор',
            'format' => 'manga',
            'price' => 499.9,
            'stock' => 3,
        ])
            ->assertCreated()
            ->assertJsonPath('data.category.id', $category->id)
            ->json('data.id');

        $this->patchJson("/api/admin/products/{$id}", ['price' => 550, 'is_published' => false])
            ->assertOk()
            ->assertJsonPath('data.price', '550.00')
            ->assertJsonPath('data.is_published', false);

        $this->deleteJson("/api/admin/products/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('products', ['id' => $id]);
    }

    public function test_product_validation_rejects_bad_data(): void
    {
        $existing = Product::factory()->create(['sku' => 'TAKEN']);

        $this->postJson('/api/admin/products', [
            'category_id' => 999,
            'sku' => 'TAKEN',
            'title' => '',
            'author' => 'Автор',
            'format' => 'comic',
            'price' => -1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id', 'sku', 'title', 'format', 'price']);

        // При обновлении товар может оставить свой же SKU
        $this->patchJson("/api/admin/products/{$existing->id}", ['sku' => 'TAKEN'])->assertOk();
    }

    public function test_product_with_orders_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();
        OrderItem::factory()->create(['product_id' => $product->id]);

        $this->deleteJson("/api/admin/products/{$product->id}")->assertStatus(409);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_admin_manages_categories(): void
    {
        $id = $this->postJson('/api/admin/categories', ['name' => 'Сёнэн', 'slug' => 'shonen'])
            ->assertCreated()
            ->json('data.id');

        $this->postJson('/api/admin/categories', ['name' => 'Сёнэн', 'slug' => 'shonen'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'slug']);

        $this->patchJson("/api/admin/categories/{$id}", ['name' => 'Сёнэн-экшен'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Сёнэн-экшен');

        Product::factory()->create(['category_id' => $id]);
        $this->deleteJson("/api/admin/categories/{$id}")->assertStatus(409);

        Product::query()->where('category_id', $id)->delete();
        $this->deleteJson("/api/admin/categories/{$id}")->assertNoContent();
    }

    public function test_category_list_rejects_invalid_per_page(): void
    {
        $this->getJson('/api/admin/categories?per_page=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_catalog_search_treats_wildcards_literally(): void
    {
        Product::factory()->create(['title' => 'Скидка 100% на всё', 'author' => 'А']);
        Product::factory()->create(['title' => 'Обычная книга', 'author' => 'Б']);

        $this->getJson('/api/products?search='.urlencode('%'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Скидка 100% на всё');

        $this->getJson('/api/products?search=_')->assertJsonCount(0, 'data');
    }
}
