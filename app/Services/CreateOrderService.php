<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateOrderService
{
    public function handle(User $user, array $data): Order
    {
        $items = collect($data['items'])->sortBy('product_id');

        $order = DB::transaction(function () use ($user, $data, $items): Order {
            $order = new Order([
                'shipping_name' => $data['shipping_name'],
                'shipping_address' => $data['shipping_address'],
            ]);

            $order->user()->associate($user);
            $order->save();

            foreach ($items as $item) {
                $product = Product::query()
                    ->whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantity = $item['quantity'];

                if (! $product->is_published) {
                    Log::warning('Order creation rejected: product is unpublished', [
                        'user_id' => $user->getKey(),
                        'product_id' => $product->getKey(),
                    ]);

                    throw new BusinessException(
                        "Товар «{$product->title}» недоступен."
                    );
                }

                if ($product->stock < $quantity) {
                    Log::warning('Order creation rejected: insufficient stock', [
                        'user_id' => $user->getKey(),
                        'product_id' => $product->getKey(),
                        'requested_quantity' => $quantity,
                        'available_stock' => $product->stock,
                    ]);

                    throw new BusinessException(
                        "Недостаточно товара «{$product->title}» на складе."
                    );
                }

                $product->decrement('stock', $quantity);

                $order->items()->create([
                    'product_id' => $product->getKey(),
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ]);
            }

            return $order;
        });

        $order = $order->refresh()->load('items.product');

        Log::info('Order created', [
            'order_id' => $order->getKey(),
            'user_id' => $user->getKey(),
            'items_count' => $order->items->count(),
            'total_quantity' => (int) $order->items->sum('quantity'),
        ]);

        return $order;
    }
}