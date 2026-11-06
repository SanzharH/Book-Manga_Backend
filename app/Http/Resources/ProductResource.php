<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'title' => $this->title,
            'author' => $this->author,
            'format' => $this->format,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'image_path' => $this->image_path,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'tags' => $this->whenLoaded(
                'tags',
                fn () => $this->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
