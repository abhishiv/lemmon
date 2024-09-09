<?php

namespace App\Http\Services;

use App\Jobs\ExportRestaurantStatistics;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDashboardService {
    public function restaurants() {
        // Restaurant list
        $restaurants = Restaurant::orderBy('name','ASC')->pluck('name', 'id');

        return $restaurants;
    }

    public function statistics(Request $request = null) {
        $restaurant_id = null;
        $statistics = [
            'order_total_count' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED])->whereNull('is_grouped')->orWhereNotNull('parent_id'),
            'order_cash_count' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED])->whereNull('is_grouped')->orWhereNotNull('parent_id'),
            'order_card_count' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED])->whereNull('is_grouped')->orWhereNotNull('parent_id'),

            'order_total_money' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED])->whereNull('is_grouped')->orWhereNotNull('parent_id'),
            'order_cash_money' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED])->whereNull('is_grouped')->orWhereNotNull('parent_id'),
            'order_card_money' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED])->whereNull('is_grouped')->orWhereNotNull('parent_id'),
            'order_tip_money' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED])->whereNull('is_grouped')->orWhereNotNull('parent_id'),
        ];
        $restaurant_name = null;

        if(!$request) {
            // If the stats requested are for all the restaurants
            $startDate = Carbon::now()->startOfMonth()->startOfDay();
            $endDate = Carbon::now();

            $restaurant_name = __('labels.all-restaurants');
        }
        else {
            // If the stats requested are for a specfic restaurant
            $restaurant_id = $request->input('restaurant') != 'restaurant' ? $request->input('restaurant') : null;

            $restaurant_name = $restaurant_id ? Restaurant::find($restaurant_id)->name  : __('labels.all-restaurants');

            $startDate = Carbon::createFromFormat('Y/m/d H:i', $request->input('startDate'));
            $endDate = Carbon::createFromFormat('Y/m/d H:i', $request->input('endDate'));

            
            if($restaurant_id) {
                foreach ($statistics as $key => $value) {
                    $statistics[$key] = $value->where('restaurant_id', $restaurant_id);
                }
            }
        }


        // Statistics

        $statistics['order_total_count'] = $statistics['order_total_count']->whereBetween('created_at', [$startDate, $endDate])->count();
        $statistics['order_cash_count'] = $statistics['order_cash_count']->where('payment_method', Order::CASH)->whereBetween('created_at', [$startDate, $endDate])->count();
        $statistics['order_card_count'] = $statistics['order_card_count']->where('payment_method', Order::ONLINE)->whereBetween('created_at', [$startDate, $endDate])->count();

        // Order total (all, cash, card)
        $statistics['order_total_money'] = $statistics['order_total_money']->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $statistics['order_cash_money'] = $statistics['order_cash_money']->where('payment_method', Order::CASH)->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $statistics['order_card_money'] = $statistics['order_card_money']->where('payment_method', Order::ONLINE)->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $statistics['order_tip_money'] = $statistics['order_tip_money']->whereBetween('created_at', [$startDate, $endDate])->sum('tips');

        $statistics['date_from'] = $startDate->isoFormat('MMM Do YYYY');
        $statistics['date_to'] = $endDate->isoFormat('MMM Do YYYY');

        $statistics['restaurant_name'] = $restaurant_name;

        return $statistics;
    }

    public function exportStatistics($request) {
        $restaurant = null;

        if($request->input('restaurant') != 'restaurant') {
            $restaurant = Restaurant::find($request->input('restaurant'));
            if(!$restaurant) {
                return false;
            }

            $restaurant = $restaurant->id;
        }

        // Create a new job
        $restaurantJob = new RestaurantJob;
        $restaurantJob->restaurant_id = $restaurant;
        $restaurantJob->user_id = request()->user()->id;
        $restaurantJob->type = 'export-statistics';
        $restaurantJob->status = RestaurantJob::UNPROCESSED;
        $restaurantJob->content = [
            'startDate' => $request->input('startDate'),
            'endDate' => $request->input('endDate')
        ];
        $restaurantJob->save();

        // Dispatch job
        ExportRestaurantStatistics::dispatch($restaurantJob);

        return $restaurantJob->id;
    }

    public function getExportStatistics($request) {
        $restaurantJob = RestaurantJob::find($request->input('job_id'));

        // Validation
        if(!$restaurantJob) {
            return 'invalid';
        }

        if($restaurantJob->user_id != request()->user()->id) {
            return 'invalid';
        }

        if($restaurantJob->status == RestaurantJob::FINISHED) {
            // job has been finished and the file has been downloaded
            return 'invalid';
        }

        // If the job still hasn't been processed
        if($restaurantJob->status == RestaurantJob::UNPROCESSED) {
            return [
                'status' => 'processing'
            ];
        }

        // This means the job has been processed

        // Validation if the result is null
        if($restaurantJob->result == null) {
            return 'invalid';
        }

        return [
            'status' => 'processed',
            'result' => route('admin.statistics.download', ['restaurantJob' => $restaurantJob->id])
        ];
    }

    public function downloadExportStatistics(RestaurantJob $restaurantJob) {
        // Verify if the file has been downloaded or if the user isn't the same
        if($restaurantJob->status != RestaurantJob::PROCESSED || $restaurantJob->user_id != request()->user()->id) {
            return new JsonResponse([
                'response' => 'Unavailable request.'
            ]);
        }

        // Mark the restaurant job as finished
        $restaurantJob->update([
            'status' => RestaurantJob::FINISHED
        ]);

        if(!Storage::exists("/order-exports/".$restaurantJob->result)) {
            return abort(404);
        }

        return response()->download(Storage::path("/order-exports/".$restaurantJob->result));
    }
}