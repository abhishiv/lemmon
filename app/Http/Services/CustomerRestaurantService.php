<?php

namespace App\Http\Services;

use Illuminate\Http\JsonResponse;

class CustomerRestaurantService
{
    public function index($table, $restaurant): bool
    {
        if (!session()->has('cart')) {
            session(['cart' => []]);
        }

        session(['table.id' => $table->id]);

        session(['table.url' => $table->menuUrl]);

        session(['table.hash' => $table->hash]);

        session(['restaurant.id' => $restaurant->id]);

        session(['restaurant.slug' => $restaurant->slug]);

        return true;
    }

    public function onesignal($request): JsonResponse
    {
        if(!session()->has('deviceId')){
            session(['deviceId' => $request->deviceId]);
        }
        return response()->json(['success' => true, 'deviceId' => session('deviceId')]);
    }

    public function groupOrder($request) {
        $request->validate([
            'in_group' => ['required', 'in:true,false']
        ]);

        if(!session()->has('in-group')) {
            session(['in-group' => ($request->in_group === 'true' ? true : false)]);
        }

        return response()->json(['success' => true, 'in-group' => session('in-group')]);
    }
}
