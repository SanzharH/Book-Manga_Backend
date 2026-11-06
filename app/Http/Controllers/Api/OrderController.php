<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\CreateOrderService;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->getKey())
            ->with('items.product')
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    public function store(
    StoreOrderRequest $request,
    CreateOrderService $createOrder,
): JsonResponse {
    $order = $createOrder->handle(
        $request->user(),
        $request->validated(),
    );

    return (new OrderResource($order))
        ->response()
        ->setStatusCode(201);
}

    public function show(Request $request, Order $order): OrderResource
    {
        abort_unless(
            (int) $order->user_id === (int) $request->user()->getKey(),
            404,
        );

        $order->refresh()->load('items.product');
        return new OrderResource($order);
    }
}