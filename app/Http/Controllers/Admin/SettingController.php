<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SettingFormRequest;
use App\Http\Services\SettingService;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->settingService = new SettingService();
    }

    public function list(Request $request) {
        $settings = $this->settingService->getSettings();

        return view('admin.settings.list', [
            'settings' => $settings
        ]);
    }

    public function update(SettingFormRequest $request) {

        $this->settingService->saveSettings($request);

        return back()->with('success', 'Modifications have been saved.');
    }
}
