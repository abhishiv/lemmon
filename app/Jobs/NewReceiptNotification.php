<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\ReceiptCreatedNotification;
use Barryvdh\DomPDF\PDF;
use Dompdf\Dompdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class NewReceiptNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $email;
    protected array $orders;
    protected $pdf;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orders, $pdf, $email)
    {
        $this->orders = $orders;
        $this->email = $email;
        $this->pdf = $pdf;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Notification::route('mail', $this->email)->notify(new ReceiptCreatedNotification($this->orders, $this->pdf));
    }
}
