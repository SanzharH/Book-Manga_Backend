<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutAndAdminApiTest extends TestCase
{
    use RefreshDatabase;

    private function orderPayload(Product $product, int $quantity, array $extra = []): array
    {
        return [
            'shipping_name' => 'Айгерим',
            'shipping_address' => 'Астана, ул. Кенесары, 40',
            'phone' => '+7 700 111 22 33',
            'items' => [['product_id' => $product->id, 'quantity' => $quantity]],
            ...$extra,
        ];
    }

    public function test_courier_delivery_is_charged_below_free_threshold(): void
    {
        $product = Product::factory()->create(['price' => '1000.00', 'stock' => 10]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/orders', $this->orderPayload($product, 2, [
            'delivery_method' => 'courier',
            'payment_method' => 'kaspi',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.phone', '+7 700 111 22 33')
            ->assertJsonPath('data.delivery_method', 'courier')
            ->assertJsonPath('data.payment_method', 'kaspi')
            ->assertJsonPath('data.items_total', '2000.00')
            ->assertJsonPath('data.delivery_price', '1500.00')
            ->assertJsonPath('data.total', '3500.00');
    }

    public function test_pickup_and_large_orders_have_free_delivery(): void
    {
        $product = Product::factory()->create(['price' => '5000.00', 'stock' => 10]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/orders', $this->orderPayload($product, 1, ['delivery_method' => 'pickup']))
            ->assertCreated()
            ->assertJsonPath('data.delivery_price', '0.00')
            ->assertJsonPath('data.total', '5000.00');

        $this->postJson('/api/orders', $this->orderPayload($product, 3, ['delivery_method' => 'point']))
            ->assertCreated()
            ->assertJsonPath('data.delivery_price', '0.00')
            ->assertJsonPath('data.total', '15000.00');
    }

    public function test_order_rejects_invalid_phone_and_delivery_method(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/orders', $this->orderPayload($product, 1, [
            'phone' => 'позвоните мне',
            'delivery_method' => 'teleport',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone', 'delivery_method']);
    }

    public function test_buyer_can_edit_new_order_but_not_paid_one(): void
    {
        $buyer = User::factory()->create();
        $order = Order::factory()->for($buyer)->create(['status' => 'new']);
        $paid = Order::factory()->for($buyer)->create(['status' => 'paid']);
        Sanctum::actingAs($buyer);

        $this->patchJson("/api/orders/{$order->id}", ['phone' => '+7 701 000 00 00'])
            ->assertOk()
            ->assertJsonPath('data.phone', '+7 701 000 00 00');

        $this->patchJson("/api/orders/{$paid->id}", ['shipping_address' => 'Новый адрес'])
            ->assertForbidden();
    }

    public function test_admin_can_list_only_own_orders_with_mine_flag(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $own = Order::factory()->for($admin)->create();
        Order::factory()->count(2)->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/orders')->assertJsonCount(3, 'data');

        $this->getJson('/api/orders?mine=1')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);
    }

    public function test_admin_product_list_shows_publication_and_category(): void
    {
        $hidden = Product::factory()->create(['is_published' => false]);
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->getJson('/api/admin/products')
            ->assertOk()
            ->assertJsonPath('data.0.id', $hidden->id)
            ->assertJsonPath('data.0.is_published', false)
            ->assertJsonPath('data.0.category.id', $hidden->category_id);
    }

    public function test_order_report_excludes_cancelled_orders(): void
    {
        $product = Product::factory()->create();
        $paid = Order::factory()->create(['status' => 'paid', 'delivery_price' => '1500.00']);
        $cancelled = Order::factory()->create(['status' => 'cancelled', 'delivery_price' => '1500.00']);
        OrderItem::factory()->create(['order_id' => $paid->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => '100.00']);
        OrderItem::factory()->create(['order_id' => $cancelled->id, 'product_id' => $product->id, 'quantity' => 5, 'unit_price' => '100.00']);
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->getJson('/api/admin/reports/orders')
            ->assertOk()
            ->assertJsonPath('data.total_orders', 2)
            ->assertJsonPath('data.cancelled_orders', 1)
            ->assertJsonPath('data.total_units_ordered', 2)
            ->assertJsonPath('data.total_order_value', '200.00')
            ->assertJsonPath('data.total_delivery_value', '1500.00')
            ->assertJsonPath('data.top_products.0.units_ordered', 2);
    }

    public function test_admin_categories_respect_per_page(): void
    {
        Category::factory()->count(20)->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->getJson('/api/admin/categories?per_page=100')
            ->assertOk()
            ->assertJsonCount(20, 'data');
    }

    public function test_reviews_expose_author_id_and_current_user_shape(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/products/{$review->product_id}/reviews")
            ->assertJsonPath('data.0.user.id', $user->id);

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('role', $user->role)
            ->assertJsonMissingPath('password');
    }

    public function test_public_categories_list_formats_of_published_products(): void
    {
        $shonen = Category::factory()->create(['name' => 'Сёнэн']);
        $classic = Category::factory()->create(['name' => 'Классика']);
        Category::factory()->create(['name' => 'Пустая']);
        Product::factory()->create(['category_id' => $shonen->id, 'format' => 'manga']);
        Product::factory()->create(['category_id' => $classic->id, 'format' => 'book']);
        Product::factory()->create(['category_id' => $classic->id, 'format' => 'manga', 'is_published' => false]);

        $formats = collect($this->getJson('/api/categories')->assertOk()->json('data'))
            ->pluck('formats', 'name');

        $this->assertSame(['manga'], $formats['Сёнэн']);
        $this->assertSame(['book'], $formats['Классика']);
        $this->assertSame([], $formats['Пустая']);
    }
}
