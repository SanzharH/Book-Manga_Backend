<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusService
{
    public function handle(Order $order, string $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus): Order {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status === $newStatus) {
                return $lockedOrder->load('items.product');
            }

            $allowedTransitions = [
                'new' => ['paid', 'cancelled'],
                'paid' => ['shipped'],
                'shipped' => ['delivered'],
                'delivered' => [],
                'cancelled' => [],
            ];

            abort_unless(
                in_array(
                    $newStatus,
                    $allowedTransitions[$lockedOrder->status] ?? [],
                    true
                ),
                409,
                'Недопустимый переход статуса заказа.'
            );

            if ($newStatus === 'cancelled') {
                $items = $lockedOrder->items()
                    ->orderBy('product_id')
                    ->lockForUpdate()
                    ->get();

                $products = Product::query()
                    ->whereIn('id', $items->pluck('product_id'))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $product = $products->get($item->product_id);

                    abort_unless(
                        $product,
                        409,
                        'Не удалось восстановить остаток товара.'
                    );

                    $product->stock += $item->quantity;
                    $product->save();
                }
            }

            $lockedOrder->status = $newStatus;
            $lockedOrder->save();

            return $lockedOrder->refresh()->load('items.product');
        });
    }
}