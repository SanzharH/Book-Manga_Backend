<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CreateOrderService;
use App\Services\UpdateOrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Order::class);

        $query = Order::query()
            ->with('items.product')
            ->latest()
            ->orderByDesc('id');

        // Администратор видит все заказы; с ?mine=1 — только собственные (для личного кабинета)
        if ($request->user()->role !== 'admin' || $request->boolean('mine')) {
            $query->where('user_id', $request->user()->getKey());
        }

        return OrderResource::collection($query->paginate(15));
    }

    public function store(StoreOrderRequest $request, CreateOrderService $createOrder): JsonResponse
    {
        $order = $createOrder->handle($request->user(), $request->validated());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        Gate::authorize('view', $order);

        return new OrderResource($order->load('items.product'));
    }

    public function update(UpdateOrderRequest $request, Order $order): OrderResource
    {
        // Права проверяет UpdateOrderRequest::authorize() через OrderPolicy::update
        $order->update($request->validated());

        return new OrderResource($order->refresh()->load('items.product'));
    }

    /**
     * Отмена заказа покупателем. Разрешена только из статуса new: остатки возвращаются на склад.
     * Для оплаченного или отправленного заказа сервис вернёт 409.
     */
    public function cancel(Order $order, UpdateOrderStatusService $updateOrderStatus): OrderResource
    {
        Gate::authorize('cancel', $order);

        return new OrderResource($updateOrderStatus->handle($order, 'cancelled'));
    }
}
