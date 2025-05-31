<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'is_active' => $this->faker->boolean(90),
            'discount' => $this->faker->optional()->randomFloat(2, 1, 100),
            'image' => 'https://picsum.photos/600/600?random=' . $this->faker->numberBetween(1, 1000),
            'category_id' => Category::factory(),
        ];
    }
} 