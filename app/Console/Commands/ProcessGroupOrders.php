<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessGroupOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:processgroups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process group orders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Order::where('status', Order::GROUP)
            ->where('is_grouped', 1)
            ->whereNull('parent_id')
            ->with('events')->chunk(50, function ($orders) {

            // Cache delay settings for restaurants
            $restaurantsDelay = [];

            foreach($orders as $order) {
                $restaurant_id = $order->restaurant_id;

                try {
                    if(!isset($restaurantsDelay[$restaurant_id])) {
                        $restaurantsDelay[$restaurant_id] = restaurant_settings_get('group_order_delay', $order->restaurant);
                    }
    
                    $delay = $restaurantsDelay[$restaurant_id];
        
                    if(!$delay) {
                        continue;
                    }
    
                    $startedWaiting = $order->events
                        ->where('event', 'created')
                        ->first();
    
                    if(Carbon::parse($startedWaiting->created_at)->addMinutes($delay) >= now()) {
                        continue;
                    }
                    
                    if ($order->restaurant->hasPrinterForType([Product::RESTAURANT])) {
                        // Bypass the NEW status
                        $order->restaurant_status = Order::PREPARING;
                        $order->bar_status = Order::PREPARING;

                        $order->foodStatuses()->update([
                            'status' => Order::PREPARING,
                        ]);
                    } else {
                        // Set the order status as new
                        $order->restaurant_status = Order::NEW;
                        $order->bar_status = Order::NEW;

                        $order->foodStatuses()->update([
                            'status' => Order::NEW,
                        ]);
                    }

                    $order->refreshStatuses();
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        });

        return Command::SUCCESS;
    }
}
