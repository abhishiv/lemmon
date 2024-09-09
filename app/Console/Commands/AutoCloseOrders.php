<?php

namespace App\Console\Commands;

use App\Http\Services\OrderService;
use App\Models\Order;
use App\Models\RestaurantSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class AutoCloseOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:autoclose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto close Takeaway orders';


    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        $restaurantSettings = RestaurantSetting::where("model_type", "App\Models\Restaurant")->where("key",
            "take_away_auto_close")->where("value", "yes")->get();

        if (!$restaurantSettings->isEmpty()) {
            $interval = RestaurantSetting::where("model_type", "App\Models\Restaurant")->where("key",
                "take_away_auto_close_interval")->whereNotNull("value")->pluck("value")->first();

            foreach ($restaurantSettings as $setting) {
                $orders = Order::where("restaurant_id", $setting->model_id)
                    ->whereIn("status", [Order::NEW, Order::PREPARING, Order::READY])
                    ->where("created_at", "<", Carbon::now()->subMinutes($interval))
                    ->where(function (Builder $query) {
                        $query->where("service_method", Order::TAKEAWAY)
                            ->where(function (Builder $query) {
                                $query->whereNull("table_id")
                                    ->where(function (Builder $query) {
                                        $query->where("payment_method", Order::ONLINE)
                                            ->orWhere("payment_method", Order::CASH)
                                            ->orWhere("payment_method", Order::CARD);
                                    })
                                    ->orWhereNotNull("table_id");
                            });
                    })
                    ->get();

                $orderService = new OrderService();
                $orderService->closeOrdersAndParents($orders);

            }
        }
    }
}
