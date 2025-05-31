<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    public function definition()
    {
        return [
            'question' => $this->faker->sentence(6),
            'answer' => $this->faker->paragraph(),
            'order' => $this->faker->numberBetween(0, 10),
            'is_active' => $this->faker->boolean(90),
        ];
    }
} 