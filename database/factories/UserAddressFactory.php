<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddressFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'street' => $this->faker->streetName(),
            'house' => $this->faker->buildingNumber(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'is_main' => $this->faker->boolean(20),
        ];
    }
} 