<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'order_id'      => $this->faker->numberBetween(1, 20),
            'product_id'    => $this->faker->numberBetween(1, 22),
            'quantity'      => $this->faker->numberBetween(1, 10),
            'price'         => $this->faker->numberBetween(1, 50),
            'notes'         => 'Notes',
            'status'        => 'active',
        ];
    }
}
