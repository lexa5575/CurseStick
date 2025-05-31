<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['new', 'paid', 'shipped', 'completed', 'cancelled']),
            'total' => $this->faker->randomFloat(2, 50, 5000),
            'street' => $this->faker->streetName(),
            'house' => $this->faker->buildingNumber(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'comment' => $this->faker->optional()->sentence(),
            'payment_method' => $this->faker->randomElement(['card', 'crypto', 'cash']),
        ];
    }
} 