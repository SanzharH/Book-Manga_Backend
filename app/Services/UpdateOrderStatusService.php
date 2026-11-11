<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $previousStatus = $lockedOrder->status;
            $restoredUnits = 0;

            $allowedTransitions = [
                'new' => ['paid', 'cancelled'],
                'paid' => ['shipped'],
                'shipped' => ['delivered'],
                'delivered' => [],
                'cancelled' => [],
            ];

            if (! in_array(
            $newStatus,
            $allowedTransitions[$lockedOrder->status] ?? [],
            true
            )) {
            Log::warning('Order status transition rejected', [
                'order_id' => $lockedOrder->getKey(),
                'from_status' => $lockedOrder->status,
                'to_status' => $newStatus,
            ]);

            abort(409, 'Недопустимый переход статуса заказа.');
            }

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
                    $restoredUnits += (int) $item->quantity;
                }
            }

            $lockedOrder->status = $newStatus;
            $lockedOrder->save();
            Log::info('Order status changed', [
    'order_id' => $lockedOrder->getKey(),
    'from_status' => $previousStatus,
    'to_status' => $newStatus,
    'stock_restored_units' => $restoredUnits,
]);

            return $lockedOrder->refresh()->load('items.product');
        });
    }
}