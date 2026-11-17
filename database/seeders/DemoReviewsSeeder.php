<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $buyer = User::where('email', 'buyer@example.test')->firstOrFail();

        $reviews = [
            [
                'rating' => 5,
                'body' => 'Интересная история, качественное издание. Рекомендую.',
            ],
            [
                'rating' => 4,
                'body' => 'Хорошая книга, прочитал с удовольствием.',
            ],
            [
                'rating' => 5,
                'body' => 'Понравились сюжет и оформление.',
            ],
            [
                'rating' => 4,
                'body' => 'Отличное издание. Хотелось бы продолжение.',
            ],
            [
                'rating' => 5,
                'body' => 'Одна из лучших историй в каталоге.',
            ],
        ];

        $products = Product::query()
            ->where('is_published', true)
            ->orderBy('id')
            ->take(count($reviews))
            ->get();

        foreach ($products as $index => $product) {
            Review::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'product_id' => $product->id,
                ],
                $reviews[$index]
            );
        }
    }
}
