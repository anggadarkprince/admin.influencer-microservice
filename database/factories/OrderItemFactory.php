<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_title' => $this->faker->text(30),
            'price' => $this->faker->numberBetween(1000, 100000),
            'quantity' => $this->faker->numberBetween(1, 5),
            'influencer_revenue' => $this->faker->numberBetween(100, 10000),
            'admin_revenue' => $this->faker->numberBetween(100, 1000),
        ];
    }
}
