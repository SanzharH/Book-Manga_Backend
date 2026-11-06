<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BusinessException;

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
                throw new BusinessException(
                "Товар «{$product->title}» недоступен.",
                );
                }

            if ($product->stock < $quantity) {
                throw new BusinessException(
                    "Недостаточно товара «{$product->title}» на складе.",
                );
                }

                $product->decrement('stock', $quantity);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ]);
            }

            return $order;
        });

        return $order->refresh()->load('items.product');
    }
}