<?php

namespace App\Jobs;

use App\Notifications\NewOrderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $deviceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($deviceId)
    {
        $this->deviceId = $deviceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Notification::route('OneSignal', $this->deviceId)->notify(new NewOrderNotification($this->deviceId));
    }
}
