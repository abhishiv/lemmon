<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantSettingsFormRequest;
use App\Http\Services\RestaurantSettingService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RestaurantSettingsController extends Controller
{

    public RestaurantSettingService $settingService;

    public function __construct()
    {
        $this->settingService = new RestaurantSettingService();
    }

    public function list(): Factory|View|Application
    {
        $settings = $this->settingService->getRestaurantSettings();

        return view('manager.settings.list', [
            'settings' => $settings
        ]);
    }

    public function update(RestaurantSettingsFormRequest $request): RedirectResponse
    {
        if ($request->validated()) {
            $this->settingService->saveRestaurantSettings($request);
            return back()->with(['success' => 'The settings have been updated',]);
        }

        return back()->with(['error' => 'An error occurred.']);
    }

}
