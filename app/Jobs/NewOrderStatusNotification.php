<?php

namespace App\Jobs;

use App\Notifications\OrderStatusNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class NewOrderStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $deviceId;
    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($deviceId, $order)
    {
        $this->deviceId = $deviceId;
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Notification::route('OneSignal', $this->deviceId)->notify(new OrderStatusNotification($this->deviceId, $this->order));
    }
}
