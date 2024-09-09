<?php

namespace App\Jobs;

use App\Notifications\NewDeliveryOrderCustomerNotification;
use App\Notifications\NewDeliveryOrderRestaurantNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendNewDeliveryOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $order;
    private $totals;
    private $customerEmail;
    private $restaurantEmail;
    private $receipt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private $config = [])
    {
        $this->order = $config['order'] ?? null;
        $this->totals = $config['totals'] ?? null;
        $this->customerEmail = $config['customer_email'] ?? null;
        $this->restaurantEmail = $config['restaurant_email'] ?? null;
        $this->receipt = $config['receipt'] ?? null;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->customerEmail) {
            Notification::route('mail', $this->customerEmail)->notify(new NewDeliveryOrderCustomerNotification($this->order, $this->totals, $this->receipt));
        }
        
        if ($this->restaurantEmail) {
            Notification::route('mail', $this->restaurantEmail)->notify(new NewDeliveryOrderRestaurantNotification($this->order, $this->totals));
        }
    }
}
