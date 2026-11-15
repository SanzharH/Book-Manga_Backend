<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PopularBooksCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'books'],
            [
                'name' => 'Книги',
                'description' => 'Художественная литература на русском языке.',
            ],
        );

        $products = [
            [
                'sku' => 'BOOK-ORWELL-1984',
                'title' => '1984',
                'author' => 'Джордж Оруэлл',
                'description' => 'Роман-антиутопия о жизни человека в условиях тотального контроля.',
                'price' => 3990.00,
                'stock' => 8,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/28889fa3-5782-4267-a447-0541d263fe7d.jpg',
            ],
            [
                'sku' => 'BOOK-BULGAKOV-MASTER',
                'title' => 'Мастер и Маргарита',
                'author' => 'Михаил Булгаков',
                'description' => 'Роман о Москве, загадочном иностранце, Мастере и Маргарите.',
                'price' => 4490.00,
                'stock' => 7,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/f2aaf964-fb25-4124-afb0-5d5dc4b2639c.jpg',
            ],
            [
                'sku' => 'BOOK-DOSTOEVSKY-CRIME',
                'title' => 'Преступление и наказание',
                'author' => 'Фёдор Достоевский',
                'description' => 'Роман о Родионе Раскольникове, его поступке и нравственном выборе.',
                'price' => 4290.00,
                'stock' => 6,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/744e11e8-81a5-41ff-92ff-784d2166f3b3.jpg',
            ],
            [
                'sku' => 'BOOK-SAINT-EXUPERY-LITTLE-PRINCE',
                'title' => 'Маленький принц',
                'author' => 'Антуан де Сент-Экзюпери',
                'description' => 'Философская сказка о путешествии Маленького принца и его встречах.',
                'price' => 2990.00,
                'stock' => 10,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/baf12016-0a4f-4e65-9df4-f69d068e9e22.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    ...$product,
                    'category_id' => $category->id,
                    'format' => 'book',
                    'is_published' => true,
                ],
            );
        }
    }
}