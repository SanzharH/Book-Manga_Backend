<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class PopularBooksCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'BOOK-ORWELL-1984',
                'genre' => 'dystopia',
                'title' => '1984',
                'author' => 'Джордж Оруэлл',
                'description' => 'Роман-антиутопия о жизни человека в условиях тотального контроля.',
                'price' => 3990.00,
                'stock' => 8,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/28889fa3-5782-4267-a447-0541d263fe7d.jpg',
            ],
            [
                'sku' => 'BOOK-BULGAKOV-MASTER',
                'genre' => 'classic',
                'title' => 'Мастер и Маргарита',
                'author' => 'Михаил Булгаков',
                'description' => 'Роман о Москве, загадочном иностранце, Мастере и Маргарите.',
                'price' => 4490.00,
                'stock' => 7,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/f2aaf964-fb25-4124-afb0-5d5dc4b2639c.jpg',
            ],
            [
                'sku' => 'BOOK-DOSTOEVSKY-CRIME',
                'genre' => 'classic',
                'title' => 'Преступление и наказание',
                'author' => 'Фёдор Достоевский',
                'description' => 'Роман о Родионе Раскольникове, его поступке и нравственном выборе.',
                'price' => 4290.00,
                'stock' => 6,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/744e11e8-81a5-41ff-92ff-784d2166f3b3.jpg',
            ],
            [
                'sku' => 'BOOK-SAINT-EXUPERY-LITTLE-PRINCE',
                'genre' => 'parable',
                'title' => 'Маленький принц',
                'author' => 'Антуан де Сент-Экзюпери',
                'description' => 'Философская сказка о путешествии Маленького принца и его встречах.',
                'price' => 2990.00,
                'stock' => 10,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/baf12016-0a4f-4e65-9df4-f69d068e9e22.jpg',
            ],
            [
                'sku' => 'BOOK-ZAMYATIN-WE',
                'genre' => 'dystopia',
                'title' => 'Мы',
                'author' => 'Евгений Замятин',
                'description' => 'Первая великая антиутопия XX века: Единое Государство, где у людей вместо имён номера, а жизнь расписана по Часовой Скрижали.',
                'price' => 2490.00,
                'stock' => 9,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/ce2dd9ae-5b53-4d20-99c7-bdc9b830024f.jpg',
            ],
            [
                'sku' => 'BOOK-WILDE-DORIAN-GRAY',
                'genre' => 'classic',
                'title' => 'Портрет Дориана Грея',
                'author' => 'Оскар Уайльд',
                'description' => 'Роман о юноше, который сохраняет красоту и молодость, пока его портрет стареет и отражает все его пороки.',
                'price' => 3290.00,
                'stock' => 8,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/57751360-94a8-4dad-8dd4-6f03bd0097db.jpg',
            ],
            [
                'sku' => 'BOOK-LONDON-MARTIN-EDEN',
                'genre' => 'bildungsroman',
                'title' => 'Мартин Иден',
                'author' => 'Джек Лондон',
                'description' => 'История моряка, который упорным трудом становится писателем и обнаруживает, что успех не приносит счастья.',
                'price' => 3490.00,
                'stock' => 7,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/e9c8f7c1-8083-4cc5-89db-d9dbb00a6f83.jpg',
            ],
            [
                'sku' => 'BOOK-BRONTE-JANE-EYRE',
                'genre' => 'bildungsroman',
                'title' => 'Джейн Эйр',
                'author' => 'Шарлотта Бронте',
                'description' => 'Сирота Джейн Эйр становится гувернанткой в поместье Торнфилд-холл, где её ждут любовь и мрачная тайна.',
                'price' => 3490.00,
                'stock' => 8,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/5973dcfd-56d0-43a1-9d9e-ca45962a969c.jpg',
            ],
            [
                'sku' => 'BOOK-ALCOTT-LITTLE-WOMEN',
                'genre' => 'bildungsroman',
                'title' => 'Маленькие женщины',
                'author' => 'Луиза Мэй Олкотт',
                'description' => 'Четыре сестры Марч взрослеют в годы Гражданской войны в США, ищут своё призвание и учатся поддерживать друг друга.',
                'price' => 3290.00,
                'stock' => 9,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/f0e4c6cb-da44-4795-816a-34141b5934bd.jpg',
            ],
            [
                'sku' => 'BOOK-ROBERTS-SHANTARAM',
                'genre' => 'adventure',
                'title' => 'Шантарам',
                'author' => 'Грегори Дэвид Робертс',
                'description' => 'Беглый заключённый из Австралии скрывается в трущобах Бомбея и оказывается втянут в жизнь мафии.',
                'price' => 4990.00,
                'stock' => 6,
                'image_path' => 'https://cdn.azbooka.ru/cv/w1100/79dc6e55-4f64-43e1-aecb-ad8f5f7ca3d1.jpg',
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
                    'format' => 'book',
                    'is_published' => true,
                ],
            );
        }
    }
}
