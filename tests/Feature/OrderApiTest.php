<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_endpoints_require_authentication(): void
    {
        $this->getJson('/api/orders')->assertUnauthorized();

        $this->postJson('/api/orders', [])->assertUnauthorized();
    }

    public function test_buyer_can_create_order_and_server_price_is_used(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create([
            'price' => '125.50',
            'stock' => 5,
        ]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson('/api/orders', [
            'shipping_name' => 'Тестовый покупатель',
            'shipping_address' => 'Тестовый адрес',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => '1.00',
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.items.0.unit_price', '125.50');

        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('data.id'),
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '125.50',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 3,
        ]);
    }

    public function test_order_is_rejected_when_stock_is_insufficient(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);

        Sanctum::actingAs($buyer);

        $this->postJson('/api/orders', [
            'shipping_name' => 'Тестовый покупатель',
            'shipping_address' => 'Тестовый адрес',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ])->assertStatus(409);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
        ]);
    }

    public function test_order_quantity_cannot_exceed_99(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 150]);

        Sanctum::actingAs($buyer);

        $this->postJson('/api/orders', [
            'shipping_name' => 'Тестовый покупатель',
            'shipping_address' => 'Тестовый адрес',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 100,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items.0.quantity');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_buyer_sees_only_their_own_orders(): void
    {
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();

        $ownOrder = Order::factory()->create([
            'user_id' => $buyer->id,
            'status' => 'new',
        ]);

        Order::factory()->create([
            'user_id' => $otherBuyer->id,
            'status' => 'new',
        ]);

        Sanctum::actingAs($buyer);

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownOrder->id);
    }

    public function test_buyer_cannot_view_another_buyers_order(): void
    {
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $otherBuyer->id,
            'status' => 'new',
        ]);

        Sanctum::actingAs($buyer);

        $this->getJson("/api/orders/{$order->id}")
            ->assertForbidden();
    }

    public function test_buyer_cannot_access_admin_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'role' => 'buyer',
        ]));

        $this->getJson('/api/admin/categories')
            ->assertForbidden();
    }

    public function test_admin_cancelling_new_order_restores_stock_once(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $product = Product::factory()->create([
            'stock' => 3,
        ]);

        $order = Order::factory()->create([
            'status' => 'new',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
        ]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'cancelled',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 5,
        ]);

        $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'paid',
        ])->assertStatus(409);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 5,
        ]);
    }

    public function test_admin_status_must_be_supported(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $order = Order::factory()->create([
            'status' => 'new',
        ]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'unknown',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }
}