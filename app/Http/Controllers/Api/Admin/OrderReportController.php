<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderReportController extends Controller
{
    public function index(): JsonResponse
    {
        $ordersByStatus = Order::query()
            ->select('status')
            ->selectRaw('COUNT(*) as orders_count')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn ($order): array => [
                'status' => $order->status,
                'count' => (int) $order->orders_count,
            ]);

        // Продажи считаются только по неотменённым заказам: отменённые товары вернулись на склад
        $soldItems = fn () => DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled');

        $topProducts = $soldItems()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('products.id', 'products.title')
            ->selectRaw('SUM(order_items.quantity) as units_ordered')
            ->selectRaw('SUM(order_items.quantity * order_items.unit_price) as ordered_value')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('units_ordered')
            ->limit(5)
            ->get()
            ->map(fn (object $product): array => [
                'product_id' => (int) $product->id,
                'title' => $product->title,
                'units_ordered' => (int) $product->units_ordered,
                'ordered_value' => $this->money($product->ordered_value),
            ]);

        $totals = $soldItems()
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units')
            ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as value')
            ->first();

        $deliveryValue = Order::query()
            ->where('status', '!=', 'cancelled')
            ->sum('delivery_price');

        return response()->json([
            'data' => [
                'total_orders' => Order::query()->count(),
                'cancelled_orders' => Order::query()->where('status', 'cancelled')->count(),
                'orders_by_status' => $ordersByStatus,
                'total_units_ordered' => (int) $totals->units,
                'total_order_value' => $this->money($totals->value),
                'total_delivery_value' => $this->money($deliveryValue),
                'top_products' => $topProducts,
            ],
        ]);
    }

    private function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
