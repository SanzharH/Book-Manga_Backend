<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::factory()->count(5)->create();
        $tags = Tag::factory()->count(8)->create();

        $products = Product::factory()
            ->count(20)
            ->recycle($categories)
            ->create();

  foreach ($products as $product) {
    $tagCount = fake()->numberBetween(1, 3);

    $product->tags()->sync(
        $tags->random($tagCount)->modelKeys()
    );
}
    }
}
