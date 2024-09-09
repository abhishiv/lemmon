<?php

namespace App\Http\Services;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RestaurantDetailsService
{
    public function update(Restaurant $restaurant, $request): Restaurant
    {
        $validated = $request->validated();

        $restaurant->update($validated);

        if (!$request->logo && !$request->old_logo) {
            if ($restaurant->receipt_logo) {
                $this->deleteLogo($restaurant);
            }
            
            $restaurant->receipt_logo = null;
            $restaurant->save();
        } elseif ($request->logo) {
            $logo = $this->uploadLogo($restaurant, $request->logo);

            if ($logo) {
                $this->deleteLogo($restaurant);

                $restaurant->receipt_logo = $logo;
                $restaurant->save();
            }
        }

        if (!$request->welcome_screen_image && !$request->old_welcome_screen_image) {
            if ($restaurant->welcome_screen_image) {
                $this->deleteWelcomeScreen($restaurant);
            }
            
            $restaurant->welcome_screen_image = null;
            $restaurant->save();
        } elseif ($request->welcome_screen_image) {
            $welcome_screen_image = $this->uploadWelcomeScreen($restaurant, $request->welcome_screen_image);

            if ($welcome_screen_image) {
                $this->deleteWelcomeScreen($restaurant);

                $restaurant->welcome_screen_image = $welcome_screen_image;
                $restaurant->save();
            }
        }

        return $restaurant;
    }

    public function uploadLogo(Restaurant $restaurant, $logo)
    {
        if (!str_contains($logo, ';base64')) {
            return false;
        }

        $data = substr($logo, strpos($logo, ',') + 1);
        $data = base64_decode($data);
        $filename = 'restaurant_' . $restaurant->id . '_logo_' . Str::random(12) . ".png";

        $path = '/' . $restaurant->id . '/receipt/' . $filename;

        Storage::disk('public_uploads')->put($path, $data);

        return $filename;
    }

    public function deleteLogo(Restaurant $restaurant)
    {
        $path = $restaurant->id . '/receipt/' . $restaurant->receipt_logo;

        if (Storage::disk('public_uploads')->exists($path)) {
            Storage::disk('public_uploads')->delete($path);
        }

        return true;
    }

    public function uploadWelcomeScreen(Restaurant $restaurant, $image)
    {
        if (!str_contains($image, ';base64')) {
            return false;
        }

        $extension = explode('/', mime_content_type($image))[1];

        $data = substr($image, strpos($image, ',') + 1);
        $data = base64_decode($data);

        $filename = 'restaurant_' . $restaurant->id . '_image_' . Str::random(12) . '.' . $extension;

        $path = '/' . $restaurant->id . '/images/' . $filename;

        Storage::disk('public_uploads')->put($path, $data);

        return $filename;
    }

    public function deleteWelcomeScreen(Restaurant $restaurant)
    {
        $path = $restaurant->id . '/images/' . $restaurant->welcome_screen_image;

        if (Storage::disk('public_uploads')->exists($path)) {
            Storage::disk('public_uploads')->delete($path);
        }

        return true;
    }
}
