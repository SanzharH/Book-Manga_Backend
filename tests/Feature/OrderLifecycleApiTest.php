<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderLifecycleApiTest extends TestCase
{
    use RefreshDatabase;

    private function orderWithItem(User $owner, string $status, Product $product, int $quantity = 2): Order
    {
        $order = Order::factory()->for($owner)->create(['status' => $status]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
        ]);

        return $order;
    }

    public function test_buyer_cancels_new_order_and_stock_is_restored(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);
        $order = $this->orderWithItem($buyer, 'new', $product);
        Sanctum::actingAs($buyer);

        $this->postJson("/api/orders/{$order->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);

        // Повторная отмена ничего не меняет и не возвращает остаток второй раз
        $this->postJson("/api/orders/{$order->id}/cancel")->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
    }

    public function test_buyer_cannot_cancel_paid_order(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);
        $order = $this->orderWithItem($buyer, 'paid', $product);
        Sanctum::actingAs($buyer);

        $this->postJson("/api/orders/{$order->id}/cancel")->assertStatus(409);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    }

    public function test_buyer_cannot_cancel_someone_elses_order(): void
    {
        $order = $this->orderWithItem(User::factory()->create(), 'new', Product::factory()->create());
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/orders/{$order->id}/cancel")->assertForbidden();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'new']);
    }

    public function test_guest_cannot_cancel_order(): void
    {
        $order = Order::factory()->create(['status' => 'new']);

        $this->postJson("/api/orders/{$order->id}/cancel")->assertUnauthorized();
    }

    public function test_admin_edits_shipping_only_before_shipment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $paid = Order::factory()->create(['status' => 'paid']);
        $shipped = Order::factory()->create(['status' => 'shipped']);
        $delivered = Order::factory()->create(['status' => 'delivered']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/orders/{$paid->id}", ['shipping_address' => 'Новый адрес'])
            ->assertOk()
            ->assertJsonPath('data.shipping_address', 'Новый адрес');

        $this->patchJson("/api/orders/{$shipped->id}", ['shipping_address' => 'Поздно'])->assertForbidden();
        $this->patchJson("/api/orders/{$delivered->id}", ['shipping_address' => 'Поздно'])->assertForbidden();
    }
}
