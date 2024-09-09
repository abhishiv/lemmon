<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRestaurantStatisticsRequest;
use App\Http\Requests\GetExportStatisticsRequest;
use App\Http\Services\ManagerDashboardService;
use App\Models\Order;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantJob;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected ManagerDashboardService $managerDashboardService)
    {

    }
    public function overview(): Factory|View|Application
    {
        $message = $this->managerDashboardService->getRestaurantMessages();

        $stats = $this->managerDashboardService->getRestaurantStatistics();

        $restaurant = Restaurant::find(request()->user()->restaurant_id);

        return view('manager.dashboard.index', compact('message', 'stats', 'restaurant'));
    }

    public function statistics(Request $request) {
        $statistics = $this->managerDashboardService->getRestaurantStatistics($request);

        return new JsonResponse($statistics);
    }

    public function exportStatistics(ExportRestaurantStatisticsRequest $request) {
        $job_id = $this->managerDashboardService->exportStatistics($request);

        return new JsonResponse(['success' => true, 'job_id' => $job_id]);
    }

    public function getExportStatistics(GetExportStatisticsRequest $request) {
        $response = $this->managerDashboardService->getExportStatistics($request);

        if($response == 'invalid') {
            return new JsonResponse(['status' => 'invalid']);
        }

        return new JsonResponse($response);
    }

    public function downloadExportStatistics(RestaurantJob $restaurantJob) {
        return $this->managerDashboardService->downloadExportStatistics($restaurantJob);
    }
}
