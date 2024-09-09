<?php

use App\Models\Menu;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\RestaurantSetting;

function priceFormat($value, $decimalSeparator = '.', $thousandsSeparator = '', $decimals = 2,): string
{
    return number_format(floatval($value), $decimals, $decimalSeparator, $thousandsSeparator);
}

function sideBar(): array
{
    if (isset(auth()->user()->restaurant_id)) {
        $user_restaurant_id = auth()->user()->restaurant_id;

        $menu_id = Menu::where('restaurant_id', $user_restaurant_id)->get()->first()->id;
    }
    return
        [
            [
                'link' => route('admin.dashboard'),
                'title' => __('labels.dashboard'),
                'icon' => 'black-tie',
                'segment' => ['dashboard'],
                'permission' => 'list_restaurant',
                'add-btn' => false
            ],
            [
                'link' => route('admin.restaurant.list'),
                'title' => __('labels.restaurants'),
                'icon' => 'black-tie',
                'segment' => ['restaurants'],
                'permission' => 'list_restaurant',
                'add-btn' => false
            ],
            [
                'link' => route('admin.settings.list'),
                'title' => trans_choice('labels.setting', 2),
                'icon' => 'black-tie',
                'segment' => ['settings'],
                'permission' => 'edit_settings',
                'add-btn' => false
            ],
            [
                'link' => route('manager.dashboard'),
                'title' => __('labels.dashboard'),
                'icon' => 'black-tie',
                'segment' => ['dashboard'],
                'permission' => 'list_product',
                'add-btn' => false
            ],
            [
                'link' => route('manager.product.category.list'),
                'title' => __('labels.categories'),
                'icon' => 'black-tie',
                'segment' => ['product-categories'],
                'permission' => 'list_product_category',
                'add-btn' => false
            ],
            [
                'link' => route('manager.product.list'),
                'title' => __('labels.products'),
                'icon' => 'black-tie',
                'segment' => ['products'],
                'permission' => 'list_product',
                'add-btn' => false
            ],
            [
                'link' => route('manager.extra.list'),
                'title' => __('labels.extras'),
                'icon' => 'black-tie',
                'segment' => ['extras'],
                'permission' => 'list_product',
                'add-btn' => false
            ],
            [
                'link' => route('manager.course.index'),
                'title' => trans_choice('labels.course', 2),
                'icon' => 'black-tie',
                'segment' => ['course'],
                'permission' => 'list_product',
                'add-btn' => false
            ],
            [
                'link' => route('manager.service.list'),
                'title' => __('labels.service'),
                'icon' => 'black-tie',
                'segment' => ['services'],
                'permission' => 'list_service',
                'add-btn' => false
            ],
            [
                'link' => route('manager.table.list'),
                'title' => __('labels.tables'),
                'icon' => 'black-tie',
                'segment' => ['tables'],
                'permission' => 'list_table',
                'add-btn' => false
            ],
            [
                'link' => route('manager.staff.list'),
                'title' => __('labels.staff'),
                'icon' => 'black-tie',
                'segment' => ['staff'],
                'permission' => 'list_staff',
                'add-btn' => false
            ],

//            [
//                'link' => route('manager.menu.edit', $menu_id ?? '1'),
//                'title' => 'Menu',
//                'icon' => 'black-tie',
//                'segment' => ['menu'],
//                'permission' => 'add_menu',
//                'add-btn' => false
//            ],
            [
                'link' => route('manager.order.list'),
                'title' => trans_choice('labels.orders', 2),
                'icon' => 'black-tie',
                'segment' => ['orders'],
                'permission' => 'list_order',
                'add-btn' => false
            ],
            [
                'link' => route('manager.settings.list'),
                'title' => trans_choice('labels.setting', 2),
                'icon' => 'black-tie',
                'segment' => ['settings'],
                'permission' => 'manager_edit_restaurant',
                'add-btn' => false
            ],
//            [
//                'link' => route('staff.dashboard'),
//                'title' => 'Staff orders',
//                'icon' => 'black-tie',
//                'segment' => ['order'],
//                'permission' => 'staff_list_order',
//                'add-btn' => false
//            ],
//            [
//                'link' => route('staff.order.table.list'),
//                'title' => 'All Orders',
//                'icon' => 'black-tie',
//                'segment' => ['order'],
//                'permission' => 'staff_list_all_order',
//                'add-btn' => false
//            ]
           [
               'link' => route('manager.restaurant.edit', $user_restaurant_id ?? 'null'),
               'title' => 'Restaurant',
               'icon' => 'black-tie',
               'segment' => ['Restaurant'],
               'permission' => 'manager_edit_restaurant',
               'add-btn' => false
           ],
        ];
}

// RestaurantSettings helper
if (!function_exists('restaurant_settings_get')) {
    /** Get the value of a setting for a specific restaurant
     * @param string $key
     * @param null $restaurant_id
     * @param mixed $defaultValue - return this if there is not a setting saved
     * @return false|null|string
     */
    function restaurant_settings_get(string $key, $restaurant = null, mixed $defaultValue = null): bool|string|null|array
    {
        if (!$restaurant) {
            $restaurant = Restaurant::find(auth()->user()->restaurant_id);
        }
        
        if (!$restaurant) {
            return false;
        }

        $setting = $restaurant->settings()->where('key', $key)->first();

        if (!$setting) {
            return RestaurantSetting::DEFAULT_VALUES[$key] ?? $defaultValue ?? null;
        }

        return $setting->getValue();
    }
}

if (!function_exists('restaurant_settings_save')) {
    /** Save a restaurant's setting
     * @param string $key
     * @param mixed $value
     * @param null $restaurant_id
     * @return bool
     */
    function restaurant_settings_save(string $key, mixed $value, $restaurant_id = null): bool
    {
        if (!$restaurant_id) {
            $restaurant_id = auth()->user()->restaurant_id;
        }
        if (!$restaurant = Restaurant::find($restaurant_id)) {
            return false;
        }

        $setting = $restaurant->settings()->where('key', $key)->first();

        if (!$setting) {
            $setting = new RestaurantSetting();
            $setting->key = $key;
            $setting->model()->associate($restaurant)->save();
            $setting->setValue($value);
            $setting->save();

            return true;
        }

        $setting->setValue($value);
        $setting->save();
        return true;
    }
}
