<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'format' => fake()->randomElement(['book', 'manga']),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 1, 500),
            'stock' => fake()->numberBetween(0, 100),
            'image_path' => null,
            'is_published' => true,
        ];
    }
}