<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $items = collect($data['items'])->sortBy('product_id');

        $order = DB::transaction(function () use ($request, $data, $items): Order {
            $order = new Order([
                'shipping_name' => $data['shipping_name'],
                'shipping_address' => $data['shipping_address'],
            ]);

            $order->user()->associate($request->user());
            $order->save();

            foreach ($items as $item) {
                $product = Product::query()
                    ->whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantity = $item['quantity'];

                if (! $product->is_published) {
                    throw ValidationException::withMessages([
                        'items' => ["Товар «{$product->title}» недоступен."],
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Недостаточно товара «{$product->title}» на складе."],
                    ]);
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

        $order->load('items.product');

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