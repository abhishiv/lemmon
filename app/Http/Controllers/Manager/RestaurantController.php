<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantDetailsRequest;
use App\Http\Requests\LogoFormRequest;
use App\Http\Requests\WelcomeScreenImageFormRequest;
use App\Http\Services\RestaurantDetailsService;
use App\Models\Restaurant;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;


class RestaurantController extends Controller
{
    public function __construct(protected RestaurantDetailsService $restaurantService)
    {
    }

    public function edit(Restaurant $restaurant): Factory|View|Application
    {
        return view('manager.restaurant.edit', compact('restaurant'));
    }

    public function update(RestaurantDetailsRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $this->restaurantService->update($restaurant, $request);

        return redirect()->route('manager.restaurant.edit', ['restaurant' => $restaurant->id])->with(['success' => 'The restaurant has been updated']);

    }

    public function getLogo(Restaurant $restaurant): bool|string
    {
        return json_encode($restaurant->getLogo());
    }

    public function verifyLogo(LogoFormRequest $request): JsonResponse
    {
        return response()->json(['success' => 'ok'], 200);
    }

    public function getAppWelcomeScreenImage(Restaurant $restaurant): bool|string
    {
        return json_encode($restaurant->getAppWelcomeScreenImage());
    }

    public function verifyAppWelcomeScreenImage(WelcomeScreenImageFormRequest $request): JsonResponse
    {
        return response()->json(['success' => 'ok'], 200);
    }
}