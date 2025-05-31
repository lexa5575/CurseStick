<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'image' => $this->faker->imageUrl(600, 600, 'products', true, 'Product'),
            'order' => $this->faker->numberBetween(0, 5),
        ];
    }
} 