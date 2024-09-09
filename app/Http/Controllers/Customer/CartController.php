<?php

namespace App\Http\Controllers\Customer;

use App\Models\RestaurantSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use App\Http\Services\CartService;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Carbon\Carbon;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function get(): Response|Application|ResponseFactory|bool
    {
        if(empty(session('restaurant.id')) || empty(session('table.url'))) {
            return response(view('errors.custom', [
                'text' => __('labels.no-table-in-session')
            ]));
        }

        $products = $this->cartService->products();

        $total = $this->cartService->total();
        $totalItems = session()->has('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0;

        $notes = $this->cartService->notes();

        $recommendedAmounts = $this->cartService->getTipsRecommendedAmounts();

        $table = RestaurantTable::where('id', session('table.id'))->with('restaurant')->first();

        $delivery = [
            'active' => restaurant_settings_get('delivery', $table->restaurant) === 'yes',  
        ];

        if ($delivery['active']) {
            $delivery['cities'] = $table->type === RestaurantTable::OFFSITE ? restaurant_settings_get('delivery_cities', $table->restaurant) : false;
        }

        $takeaway = [
            'active' => restaurant_settings_get('take_away', $table->restaurant) === 'yes',
        ];

        if ($takeaway['active']) {
            $takeaway['pickup_options'] = [
                'days' => $table->type == RestaurantTable::OFFSITE ? $this->getPickupDays() : false,
                'times' => $table->type == RestaurantTable::OFFSITE ? $this->getPickupTimes($table->restaurant->getWorkingSchedule()) : false,
                'currentTime' => Carbon::now('Europe/Zurich'),
            ];
        }
        
        return response(view('customer.cart', compact('products', 'table', 'total', 'totalItems', 'notes', 'recommendedAmounts', 'delivery', 'takeaway')))->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    public function add(Request $request): JsonResponse
    {
        $count = $this->cartService->add($request);

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function update(Request $request): JsonResponse
    {
        $response = $this->cartService->update($request);

        return response()->json(['success' => true, 'total' => $response['total'], 'productTotal' => $response['productTotal']]);
    }

    public function dine(Request $request): JsonResponse
    {
        $response = $this->cartService->dine($request);
        return response()->json(['success' => true, 'total' => $response['total'], 'takeaway_discount' => $response['takeaway_discount'] ?? 0]);
    }

    public function refresh(Request $request): JsonResponse
    {
        if (!$request->customer_type) {
            return response()->json(['success' => false]);
        }

        $totals = $this->cartService->getTotals($request);

        return response()->json(['success' => true, 'totals' => $totals]);
    }

    public function delete(Request $request): JsonResponse
    {
        $total = $this->cartService->delete($request);

        return response()->json(['success' => true, 'total' => $total]);
    }

    public function destroy(): JsonResponse
    {
        session(['cart' => []]);
        session(['quantity' => []]);

        return response()->json(['success' => true]);
    }

    private function getPickupDays()
    {
        $dates = [];
        $today = Carbon::today();
        
        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->addDays($i);
            $dates[] = $date->format('D, j M');
        }

        return $dates;
    }

    private function getPickupTimes($schedule)
    {
        $workingHours = $schedule;
        $intervalInMinutes = 15;
        $startTime = $workingHours[0];
        $endTime = $workingHours[1];

        $pickupTimes = [];

        $time = clone $startTime;

        while ($time <= $endTime) {
            $pickupTimes[] = $time->format('H:i');
            $time->addMinutes($intervalInMinutes);
        }

        return $pickupTimes;
    }
}
