<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GenreCategoriesSeeder::class,
            TatsukiFujimotoCatalogSeeder::class,
            PopularMangaCatalogSeeder::class,
            PopularBooksCatalogSeeder::class,
            DemoUsersSeeder::class,
            DemoReviewsSeeder::class,
        ]);

        // Все сидеры используют updateOrCreate, поэтому повторный db:seed переносит
        // существующие товары в жанры; опустевшие категории «Манга» и «Книги» удаляются.
        GenreCategoriesSeeder::removeEmptyLegacyCategories();
    }
}
