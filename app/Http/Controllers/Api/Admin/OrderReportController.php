<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
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

        $topProducts = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('products.id', 'products.title')
            ->selectRaw('SUM(order_items.quantity) as units_ordered')
            ->selectRaw(
                'SUM(order_items.quantity * order_items.unit_price) as ordered_value'
            )
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('units_ordered')
            ->limit(5)
            ->get()
            ->map(fn (object $product): array => [
                'product_id' => (int) $product->id,
                'title' => $product->title,
                'units_ordered' => (int) $product->units_ordered,
                'ordered_value' => number_format(
                    (float) $product->ordered_value,
                    2,
                    '.',
                    '',
                ),
            ]);

        $totalOrderValue = OrderItem::query()
            ->sum(DB::raw('quantity * unit_price'));

        return response()->json([
            'data' => [
                'total_orders' => Order::query()->count(),
                'orders_by_status' => $ordersByStatus,
                'total_units_ordered' => (int) OrderItem::query()->sum('quantity'),
                'total_order_value' => number_format(
                    (float) $totalOrderValue,
                    2,
                    '.',
                    '',
                ),
                'top_products' => $topProducts,
            ],
        ]);
    }
}