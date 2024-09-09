<?php

namespace App\Http\Controllers\Admin;

use App\Http\Services\AdminDashboardService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRestaurantStatisticsRequest;
use App\Http\Requests\GetExportStatisticsRequest;
use App\Models\Restaurant;
use App\Models\RestaurantJob;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected AdminDashboardService $adminDashboardService)
    {
        
    }

    public function overview(): View|Factory|Application
    {
        $restaurants = $this->adminDashboardService->restaurants();

        $stats = $this->adminDashboardService->statistics();

        return view('admin.dashboard.overview', compact('restaurants', 'stats'));
    }

    public function statistics(Request $request) {
        $stats = $this->adminDashboardService->statistics($request);

        return new JsonResponse($stats);
    }

    public function exportStatistics(ExportRestaurantStatisticsRequest $request) {
        $job_id = $this->adminDashboardService->exportStatistics($request);

        if($job_id == false) {
            return new JsonResponse(['success' => false]);
        }

        return new JsonResponse(['success' => true, 'job_id' => $job_id]);
    }

    public function getExportStatistics(GetExportStatisticsRequest $request) {
        $response = $this->adminDashboardService->getExportStatistics($request);

        if($response == 'invalid') {
            return new JsonResponse(['status' => 'invalid']);
        }

        return new JsonResponse($response);
    }

    public function downloadExportStatistics(RestaurantJob $restaurantJob) {
        return $this->adminDashboardService->downloadExportStatistics($restaurantJob);
    }

}