<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\UpdateOrderStatusService;

class OrderStatusController extends Controller
{
    public function update(
    UpdateOrderStatusRequest $request,
    Order $order,
    UpdateOrderStatusService $updateOrderStatus,
    ): OrderResource {
    $updatedOrder = $updateOrderStatus->handle(
        $order,
        $request->validated('status'),
    );

    return new OrderResource($updatedOrder);
    }
}