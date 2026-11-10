<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement([
            'new',
            'paid',
            'shipped',
            'delivered',
            'cancelled',
            ]),
            'shipping_name' => fake()->name(),
            'shipping_address' => fake()->address(),
        ];
    }
}