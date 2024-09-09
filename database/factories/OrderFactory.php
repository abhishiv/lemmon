<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'restaurant_id' => 1,
            'table_id'      => 1,
            'amount'        => $this->faker->numberBetween(10, 100),
            'notes'         => 'Notes',
            'payment_method'=> 'online',
            'device_id'     => $this->faker->uuid(),
            'status'        => 'closed',
        ];
    }
}
