<?php

namespace App\Http\Services;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantSettingService
{

    /**
     * Returns an array representing key - value pairs of settings
     * @return array
     */
    public function getRestaurantSettings(): array
    {
        $restaurant = Restaurant::find(auth()->user()->restaurant_id);
        $settings = $restaurant->settings;
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    public function saveRestaurantSettings($request): void
    {

        $this->savePrinters($request);
        $this->saveDeliveryCities($request);

        foreach ($request->safe()->except('ip', 'port', 'device-id', 'print-type', 'delivery_cities', 'delivery_fees') as $key => $value) {
            restaurant_settings_save($key, $value);
        }

        // Order grouping popup checkbox
        if (!$request->has('order_grouping_popup')) {
            restaurant_settings_save('order_grouping_popup', 'false');
        }

        // Takeaway checkbox
        if (!$request->has('take_away')) {
            restaurant_settings_save('take_away', null);
        }

        // Takeaway AutoClose checkbox
        if (!$request->has('take_away_auto_close')) {
            restaurant_settings_save('take_away_auto_close', null);
        }

        if (!$request->has('delivery')) {
            restaurant_settings_save('delivery', null);
        }
    }

    private function savePrinters($request)
    {
        $data = $request->only('ip', 'port', 'device-id', 'print-type');

        if (empty($data)) {
            $printers = 'false';
        } else {
            $printers = [];

            foreach ($data['ip'] as $key => $ip) {
                $printers[] = (object) [
                    'ip' => $ip,
                    'port' => $data['port'][$key],
                    'port' => $data['port'][$key],
                    'device_id' => $data['device-id'][$key],
                    'print_type' => $data['print-type'][$key],
                ];
            }

            $request->request->remove('ip');
            $request->request->remove('port');
            $request->request->remove('device-id');
            $request->request->remove('print-type');
        }

        restaurant_settings_save('printers', $printers);
    }

    private function saveDeliveryCities($request)
    {
        $data = $request->only('delivery_cities', 'delivery_fees');

        if (empty($data)) {
            $deliveryCities = 'false';
        } else {
            $deliveryCities = [];

            foreach ($data['delivery_cities'] as $key => $city) {
                $deliveryCities[] = (object) [
                    'name' => $city,
                    'fee' => $data['delivery_fees'][$key],
                ];
            }

            $request->request->remove('delivery_cities');
            $request->request->remove('delivery_fees');
        }

        restaurant_settings_save('delivery_cities', $deliveryCities);
    }
}
