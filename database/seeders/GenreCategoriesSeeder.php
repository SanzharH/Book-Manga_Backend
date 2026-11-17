<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Категории каталога — это жанры. Тип издания (книга или манга) хранится отдельно в products.format,
 * поэтому категории «Манга» и «Книги» его не дублируют.
 */
class GenreCategoriesSeeder extends Seeder
{
    public const GENRES = [
        'shonen' => ['Сёнэн', 'Манга о приключениях, сражениях и дружбе для подростковой аудитории.'],
        'dark-fantasy' => ['Тёмное фэнтези', 'Мрачные истории о жестоких мирах и сверхъестественных силах.'],
        'drama' => ['Драма', 'Камерные истории о людях, памяти и потере.'],
        'short-stories' => ['Сборники рассказов', 'Короткие самостоятельные истории под одной обложкой.'],
        'classic' => ['Классика', 'Произведения, проверенные временем.'],
        'dystopia' => ['Антиутопия', 'Истории об обществе, где порядок оборачивается несвободой.'],
        'parable' => ['Философская сказка', 'Притчи для читателей любого возраста.'],
        'comedy' => ['Комедия', 'Смешные истории, в которых даже сражения и шпионаж — повод для шутки.'],
        'thriller' => ['Триллер', 'Напряжённые истории о противостоянии умов и опасных тайнах.'],
        'bildungsroman' => ['Роман воспитания', 'Истории взросления героев и поиска своего места в мире.'],
        'adventure' => ['Приключения', 'Путешествия, опасности и неожиданные повороты судьбы.'],
    ];

    // Прежние категории повторяли тип издания; их удаляем, если в них не осталось товаров
    private const LEGACY_SLUGS = ['manga', 'books'];

    public function run(): void
    {
        foreach (array_keys(self::GENRES) as $slug) {
            self::category($slug);
        }
    }

    public static function category(string $slug): Category
    {
        [$name, $description] = self::GENRES[$slug];

        return Category::updateOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'description' => $description],
        );
    }

    public static function removeEmptyLegacyCategories(): void
    {
        Category::query()
            ->whereIn('slug', self::LEGACY_SLUGS)
            ->whereDoesntHave('products')
            ->delete();
    }
}
