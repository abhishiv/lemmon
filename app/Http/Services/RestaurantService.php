<?php

namespace App\Http\Services;

use App\Models\FoodType;
use App\Models\Menu;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RestaurantService
{
    public function store($request): Restaurant
    {
        $restaurant = new Restaurant();
        $restaurant->name = $request->name;
        $restaurant->email = $request->email;
        $restaurant->phone = $request->phone;
        $restaurant->address = $request->address;
        $restaurant->contact_person = $request->contact_person;
        $restaurant->bank_account = $request->bank_account;
        $restaurant->payment_fee = $request->payment_fee;
        $restaurant->vat = $request->vat;
        $restaurant->company_registration = $request->company_registration;
        $restaurant->slug = $request->slug;
        $restaurant->payrexx_token = $request->payrexx_token;
        $restaurant->payrexx_name = $request->payrexx_name;
        $restaurant->receipt_message = $request->receipt_message;
        $restaurant->status = Restaurant::PENDING;
        $restaurant->onboarded_by = auth()->id();
        $restaurant->onboarded_at = now();

        $restaurant->save();

        $logo = $this->uploadLogo($restaurant, $request->logo);

        if ($logo) {
            $restaurant->receipt_logo = $logo;
            $restaurant->save();
        }

        if ($request->submit == 'invite') {
            $this->createRestaurantManager($request, $restaurant);
        }

        Menu::create([
            'title' => 'Menu',
            'restaurant_id' => $restaurant->id,
        ]);

        return $restaurant;
    }

    public function uploadLogo($restaurant, $logo)
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

    public function update(Restaurant $restaurant, $request): Restaurant
    {
        $validated = $request->validated();

        if ($request->submit == 'invite') {
            $validated['status'] = Restaurant::PENDING;
            $this->createRestaurantManager($request, $restaurant);

            return $restaurant;
        }

        $restaurant->update($validated);

        $logo = $this->uploadLogo($restaurant, $request->logo);

        if ($logo) {
            $restaurant->receipt_logo = $logo;
            $restaurant->save();
        }

        $this->updateRestaurantManager($request, $restaurant);

        return $restaurant;
    }

    public function destroy(Restaurant $restaurant): void
    {
        $restaurant->delete();

        if (!empty($restaurant->manager->id)) {
            $user = User::find($restaurant->manager->id);
            (new UserService())->destroy($user);
        }
    }

    private function createRestaurantManager($request, $restaurant): void
    {
        $password = bcrypt(rand(0, 1000));

        $request->request->add(['name' => $request->contact_person]);
        $request->request->add(['restaurant_id' => $restaurant->id]);
        $request->request->add(['restaurant' => $restaurant]);
        $request->request->add(['role' => User::MANAGER]);
        $request->request->add(['password' => $password]);
        $request->request->add(['password_confirmation' => $password]);

        (new UserService())->store($request);
    }

    private function updateRestaurantManager($request, $restaurant): void
    {
        $user = $restaurant->manager;

        (new UserService())->update($user, $request);
    }
}
