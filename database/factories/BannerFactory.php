<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    public function definition()
    {
        return [
            'image' => $this->faker->imageUrl(1200, 400, 'banners', true, 'Banner'),
            'text' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(80),
            'order' => $this->faker->numberBetween(0, 10),
        ];
    }
} 