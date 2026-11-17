<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cents = fn ($value): int => (int) round((float) $value * 100);
        $money = fn (int $cents): string => number_format($cents / 100, 2, '.', '');
        $itemsCents = fn (): int => $this->items->sum(
            fn ($item) => $cents($item->unit_price) * $item->quantity
        );

        return [
            'id' => $this->id,
            'status' => $this->status,
            'shipping_name' => $this->shipping_name,
            'shipping_address' => $this->shipping_address,
            'phone' => $this->phone,
            'delivery_method' => $this->delivery_method ?? 'courier',
            'payment_method' => $this->payment_method ?? 'card',
            'items' => $this->whenLoaded('items', fn () => $this->items->map(
                fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'title' => $item->product?->title,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $money($cents($item->unit_price) * $item->quantity),
                ]
            )),
            'items_total' => $this->whenLoaded('items', fn () => $money($itemsCents())),
            'delivery_price' => $money($cents($this->delivery_price ?? 0)),
            'total' => $this->whenLoaded('items', fn () => $money(
                $itemsCents() + $cents($this->delivery_price ?? 0)
            )),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
