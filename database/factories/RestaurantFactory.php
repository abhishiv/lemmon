<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use Carbon\Carbon;

/**
 * @extends Factory
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'name' => "string",
        'email' => "string",
        'contact_person' => "string",
        'phone' => "string",
        'vat' => "string",
        'bank_account' => "string",
        'payment_fee' => "int",
        'status' => "string",
        'slug' => 'string',
        'onboarded_by' => "datetime",
        'onboarded_at' => "datetime",
        'address' => "string",
    ])] public function definition(): array
    {
        $restaurantsNames = [
            'Juan in a Million',
            'Burrito Belly',
            'La Mesa(the table)',
            'El Tor(the bull)',
            'Much(too much)',
            'La Ta berna(the tavern)',
            'La Ola(the pan)',
            'Delicioaso',
            'Sabres(tasty)',
        ];

        $status = Restaurant::ACTIVE;

        $name = $restaurantsNames[rand(0, 8)];

        $slug = Str::slug($name, '-');

        return [
            'name' => $name,
            'email' => $this->faker->unique()->safeEmail(),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'vat' => $this->faker->text(6),
            'bank_account' => $this->faker->text(10),
            'payment_fee' => $this->faker->numberBetween(1, 5),
            'company_registration' => $this->faker->text(6),
            'payrexx_token' => 'puR8w6oo9Avi3EoJIdkbXf12BlC1Ed',
            'company_registration' => $this->faker->text(6),
            'payrexx_name' => 'test',
            'slug' => $slug,
            'status' => $status,
            'onboarded_by' => 1,
            'onboarded_at' => Carbon::now(),
            'address' => 'address',
        ];
    }
}
