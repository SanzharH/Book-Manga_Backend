<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'shipping_name' => $this->shipping_name,
            'shipping_address' => $this->shipping_address,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(
                fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'title' => $item->product?->title,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => number_format(
                        (int) round((float) $item->unit_price * 100)
                            * $item->quantity / 100,
                        2,
                        '.',
                        '',
                    ),
                ]
            )),
            'total' => $this->whenLoaded('items', fn () => number_format(
                $this->items->sum(
                    fn ($item) => (int) round((float) $item->unit_price * 100)
                        * $item->quantity
                ) / 100,
                2,
                '.',
                '',
            )),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
