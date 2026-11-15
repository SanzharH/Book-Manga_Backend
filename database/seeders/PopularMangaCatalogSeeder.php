<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PopularMangaCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'manga'],
            [
                'name' => 'Манга',
                'description' => 'Японские графические произведения.',
            ]
        );

        $products = [
            [
                'sku' => 'MANGA-ONE-PIECE-01',
                'title' => 'Ван-Пис. Том 1',
                'author' => 'Эйитиро Ода',
                'description' => 'Первый том приключенческой манги о путешествии Манки Д. Луффи и его команды.',
                'price' => 2490.00,
                'stock' => 10,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1569319014.jpg',
            ],
            [
                'sku' => 'MANGA-NARUTO-01',
                'title' => 'Наруто. Том 1',
                'author' => 'Масаси Кисимото',
                'description' => 'Первый том истории о юном ниндзя Наруто Узумаки.',
                'price' => 2490.00,
                'stock' => 10,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1569319006.jpg',
            ],
            [
                'sku' => 'MANGA-JUJUTSU-KAISEN-01',
                'title' => 'Магическая битва. Том 1',
                'author' => 'Гэгэ Акутами',
                'description' => 'Первый том манги о Юдзи Итадори и мире проклятий.',
                'price' => 2590.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974710025.jpg',
            ],
            [
                'sku' => 'MANGA-DEMON-SLAYER-01',
                'title' => 'Истребитель демонов. Том 1',
                'author' => 'Коёхару Готогэ',
                'description' => 'Первый том истории Тандзиро Камадо, отправившегося спасать сестру и сражаться с демонами.',
                'price' => 2590.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974700526.jpg',
            ],
            [
                'sku' => 'MANGA-MY-HERO-ACADEMIA-01',
                'title' => 'Моя геройская академия. Том 1',
                'author' => 'Кохэй Хорикоси',
                'description' => 'Первый том манги о школьнике Идзуку Мидории, мечтающем стать героем.',
                'price' => 2590.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421582694.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    ...$product,
                    'category_id' => $category->id,
                    'format' => 'manga',
                    'is_published' => true,
                ]
            );
        }
    }
}
