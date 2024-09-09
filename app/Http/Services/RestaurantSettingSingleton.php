<?php

namespace App\Http\Services;

use App\Models\RestaurantSetting;

class RestaurantSettingSingleton
{
    private static ?RestaurantSettingSingleton $instance = null;
    private $settings;

    private function __construct($restaurantId)
    {
        $this->settings = RestaurantSetting::where('model_id', $restaurantId)->get()->keyBy('key')->toArray();
    }

    public static function getInstance($restaurantId): ?RestaurantSettingSingleton
    {
        if (self::$instance == null) {
            self::$instance = new RestaurantSettingSingleton($restaurantId);
        }
        return self::$instance;
    }

    public function getValue($key)
    {
        return $this->settings[$key]['value'] ?? null;
    }
}
