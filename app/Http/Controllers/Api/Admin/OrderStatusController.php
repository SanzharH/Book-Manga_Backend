<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;

class OrderStatusController extends Controller
{
    public function update(
        UpdateOrderStatusRequest $request,
        Order $order,
    ): OrderResource {
        $order->update($request->validated());

        return new OrderResource(
            $order->refresh()->load('items.product')
        );
    }
}