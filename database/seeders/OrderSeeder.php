<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Order::factory()->count(20)->create();

        RestaurantTable::create([
            'name' => 'Table 1',
            'restaurant_id' => 1,
            'hash' => Str::uuid(),
            'type' => RestaurantTable::SERVE,
            'status' => 'available',
        ]);
    }
}
