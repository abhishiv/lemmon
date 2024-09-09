<?php

namespace App\Jobs;

use App\Notifications\NewTakeawayOrderCustomerNotification;
use App\Notifications\NewTakeawayOrderRestaurantNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendNewTakeawayOrderNotification implements ShouldQueue
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
            Notification::route('mail', $this->customerEmail)->notify(new NewTakeawayOrderCustomerNotification($this->order, $this->totals, $this->receipt));
        }
        
        if ($this->restaurantEmail) {
            Notification::route('mail', $this->restaurantEmail)->notify(new NewTakeawayOrderRestaurantNotification($this->order, $this->totals));
        }
    }
}
