<?php

namespace App\Http\Services;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingService
{

    /**
     * Returns an array representing key - value pairs of settings
     * @return array
     */
    public function getSettings(): array
    {
        $settings = Setting::all();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    public function saveSettings($request): bool
    {
        foreach ($request->validated() as $key => $value) {
            $setting = Setting::where('key', $key)->first();
    
            if (!$setting) {
                $setting = new Setting();
                $setting->key = $key;
            }
    
            $setting->value = $value;
            $setting->save();
        }

        return true;
    }
}
