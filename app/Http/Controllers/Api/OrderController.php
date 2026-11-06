<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\CreateOrderService;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\UpdateOrderRequest;

class OrderController extends Controller
{
    public function index(Request $request)
{
    Gate::authorize('viewAny', Order::class);

    $query = Order::query()
        ->with('items.product')
        ->latest();

    if ($request->user()->role !== 'admin') {
        $query->where('user_id', $request->user()->getKey());
    }

    return OrderResource::collection($query->paginate(15));
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
    Gate::authorize('view', $order);

    $order->load('items.product');

    return new OrderResource($order);
}
public function update(
    UpdateOrderRequest $request,
    Order $order,
): OrderResource {
    Gate::authorize('update', $order);

    $order->update($request->validated());

    return new OrderResource($order->refresh()->load('items.product'));
}
}