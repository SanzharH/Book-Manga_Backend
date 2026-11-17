<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class TatsukiFujimotoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'FUJIMOTO-CHAINSAW-01',
                'genre' => 'shonen',
                'title' => 'Человек-бензопила. Том 1',
                'description' => 'Первый том манги Тацуки Фудзимото о Дэндзи и демоне-бензопиле Почите.',
                'price' => 2490.00,
                'stock' => 10,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974709930.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-CHAINSAW-02',
                'genre' => 'shonen',
                'title' => 'Человек-бензопила. Том 2',
                'description' => 'Второй том манги Тацуки Фудзимото о приключениях Дэндзи.',
                'price' => 2490.00,
                'stock' => 8,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974709949.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-CHAINSAW-03',
                'genre' => 'shonen',
                'title' => 'Человек-бензопила. Том 3',
                'description' => 'Третий том манги Тацуки Фудзимото.',
                'price' => 2490.00,
                'stock' => 7,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974709957.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-CHAINSAW-04',
                'genre' => 'shonen',
                'title' => 'Человек-бензопила. Том 4',
                'description' => 'Четвёртый том манги Тацуки Фудзимото.',
                'price' => 2490.00,
                'stock' => 6,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974717275.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-FIREPUNCH-01',
                'genre' => 'dark-fantasy',
                'title' => 'Огненный удар. Том 1',
                'description' => 'Первый том постапокалиптической манги Тацуки Фудзимото.',
                'price' => 2790.00,
                'stock' => 8,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421597179.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-FIREPUNCH-02',
                'genre' => 'dark-fantasy',
                'title' => 'Огненный удар. Том 2',
                'description' => 'Второй том манги Тацуки Фудзимото об Агни и его пути.',
                'price' => 2790.00,
                'stock' => 7,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421597187.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-FIREPUNCH-03',
                'genre' => 'dark-fantasy',
                'title' => 'Огненный удар. Том 3',
                'description' => 'Третий том манги Тацуки Фудзимото.',
                'price' => 2790.00,
                'stock' => 6,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421597195.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-FIREPUNCH-04',
                'genre' => 'dark-fantasy',
                'title' => 'Огненный удар. Том 4',
                'description' => 'Четвёртый том манги Тацуки Фудзимото.',
                'price' => 2790.00,
                'stock' => 5,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421598086.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-LOOKBACK',
                'genre' => 'drama',
                'title' => 'Оглянись',
                'description' => 'Самостоятельная история Тацуки Фудзимото о двух девушках, мечтающих создавать мангу.',
                'price' => 2990.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974734641.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-GOODBYE-ERI',
                'genre' => 'drama',
                'title' => 'Прощай, Эри',
                'description' => 'Самостоятельная манга Тацуки Фудзимото о памяти, кино и потере.',
                'price' => 2990.00,
                'stock' => 8,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974738930.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-BEFORE-17-21',
                'genre' => 'short-stories',
                'title' => 'До «Человека-бензопилы»: истории 17–21',
                'description' => 'Сборник ранних коротких историй Тацуки Фудзимото.',
                'price' => 2690.00,
                'stock' => 6,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974734773.jpg',
            ],
            [
                'sku' => 'FUJIMOTO-BEFORE-22-26',
                'genre' => 'short-stories',
                'title' => 'До «Человека-бензопилы»: истории 22–26',
                'description' => 'Сборник коротких историй Тацуки Фудзимото.',
                'price' => 2690.00,
                'stock' => 6,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974734781.jpg',
            ],
        ];

        foreach ($products as $product) {
            $genre = $product['genre'];
            unset($product['genre']);

            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    ...$product,
                    'category_id' => GenreCategoriesSeeder::category($genre)->id,
                    'author' => 'Татсуки Фудзимото',
                    'format' => 'manga',
                    'is_published' => true,
                ]
            );
        }
    }
}
