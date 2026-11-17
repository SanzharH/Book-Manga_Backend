<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class PopularMangaCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'MANGA-ONE-PIECE-01',
                'genre' => 'shonen',
                'title' => 'Ван-Пис. Том 1',
                'author' => 'Эйитиро Ода',
                'description' => 'Первый том приключенческой манги о путешествии Манки Д. Луффи и его команды.',
                'price' => 2490.00,
                'stock' => 10,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1569319014.jpg',
            ],
            [
                'sku' => 'MANGA-NARUTO-01',
                'genre' => 'shonen',
                'title' => 'Наруто. Том 1',
                'author' => 'Масаси Кисимото',
                'description' => 'Первый том истории о юном ниндзя Наруто Узумаки.',
                'price' => 2490.00,
                'stock' => 10,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1569319006.jpg',
            ],
            [
                'sku' => 'MANGA-JUJUTSU-KAISEN-01',
                'genre' => 'shonen',
                'title' => 'Магическая битва. Том 1',
                'author' => 'Гэгэ Акутами',
                'description' => 'Первый том манги о Юдзи Итадори и мире проклятий.',
                'price' => 2590.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974710025.jpg',
            ],
            [
                'sku' => 'MANGA-DEMON-SLAYER-01',
                'genre' => 'shonen',
                'title' => 'Истребитель демонов. Том 1',
                'author' => 'Коёхару Готогэ',
                'description' => 'Первый том истории Тандзиро Камадо, отправившегося спасать сестру и сражаться с демонами.',
                'price' => 2590.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974700526.jpg',
            ],
            [
                'sku' => 'MANGA-MY-HERO-ACADEMIA-01',
                'genre' => 'shonen',
                'title' => 'Моя геройская академия. Том 1',
                'author' => 'Кохэй Хорикоси',
                'description' => 'Первый том манги о школьнике Идзуку Мидории, мечтающем стать героем.',
                'price' => 2590.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421582694.jpg',
            ],
            [
                'sku' => 'MANGA-SPY-FAMILY-01',
                'genre' => 'comedy',
                'title' => 'Шпион × Семья. Том 1',
                'author' => 'Тацуя Эндо',
                'description' => 'Шпион, наёмная убийца и девочка-телепат изображают обычную семью — и каждый скрывает свою тайну.',
                'price' => 2690.00,
                'stock' => 12,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1974715469.jpg',
            ],
            [
                'sku' => 'MANGA-ONE-PUNCH-MAN-01',
                'genre' => 'comedy',
                'title' => 'Ванпанчмен. Том 1',
                'author' => 'ONE, Юсукэ Мурата',
                'description' => 'Сайтама побеждает любого врага одним ударом и страдает от того, что битвы больше не приносят азарта.',
                'price' => 2590.00,
                'stock' => 10,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421585642.jpg',
            ],
            [
                'sku' => 'MANGA-DEATH-NOTE-01',
                'genre' => 'thriller',
                'title' => 'Тетрадь смерти. Том 1',
                'author' => 'Цугуми Ооба, Такэси Обата',
                'description' => 'Школьник Лайт Ягами находит тетрадь, способную убить любого, чьё имя в неё вписано, и начинает дуэль с гениальным детективом L.',
                'price' => 2590.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421501686.jpg',
            ],
            [
                'sku' => 'MANGA-TOKYO-GHOUL-01',
                'genre' => 'dark-fantasy',
                'title' => 'Токийский гуль. Том 1',
                'author' => 'Суи Исида',
                'description' => 'Студент Канэки после встречи с гулем сам становится наполовину гулем и вынужден жить между двумя мирами.',
                'price' => 2690.00,
                'stock' => 8,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1421580365.jpg',
            ],
            [
                'sku' => 'MANGA-FULLMETAL-ALCHEMIST-01',
                'genre' => 'shonen',
                'title' => 'Стальной алхимик. Том 1',
                'author' => 'Хирому Аракава',
                'description' => 'Братья Элрики ищут философский камень, чтобы вернуть то, что потеряли из-за запретной алхимии.',
                'price' => 2590.00,
                'stock' => 9,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1591169208.jpg',
            ],
            [
                'sku' => 'MANGA-DRAGON-BALL-01',
                'genre' => 'shonen',
                'title' => 'Драконий жемчуг. Том 1',
                'author' => 'Акира Торияма',
                'description' => 'Мальчик Гоку и Булма отправляются на поиски семи жемчужин дракона, исполняющих любое желание.',
                'price' => 2490.00,
                'stock' => 10,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1569319200.jpg',
            ],
            [
                'sku' => 'MANGA-BLEACH-01',
                'genre' => 'shonen',
                'title' => 'Блич. Том 1',
                'author' => 'Тайто Кубо',
                'description' => 'Школьник Итиго Куросаки получает силу синигами и начинает защищать живых от злых духов.',
                'price' => 2490.00,
                'stock' => 8,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1591164419.jpg',
            ],
            [
                'sku' => 'MANGA-HUNTER-X-HUNTER-01',
                'genre' => 'shonen',
                'title' => 'Охотник × Охотник. Том 1',
                'author' => 'Ёсихиро Тогаси',
                'description' => 'Гон Фрикс сдаёт смертельно опасный экзамен на охотника, чтобы найти своего отца.',
                'price' => 2490.00,
                'stock' => 7,
                'image_path' => 'https://dw9to29mmj727.cloudfront.net/products/1591167531.jpg',
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
                    'format' => 'manga',
                    'is_published' => true,
                ]
            );
        }
    }
}
