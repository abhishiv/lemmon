<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptCreatedNotification extends Notification
{
    use Queueable;

    protected array $orders;
    protected $pdf;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $orders, $pdf)
    {
        $this->orders = $orders;
        $this->pdf = $pdf;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Headers
     */

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'Content-Type'      => 'multipart/form-data'
            ],
        );
    }

    public function toMail($notifiable)
    {

        $email = (new MailMessage)->view('emails.receipt', ['orders' => $this->orders])->subject('Your order receipt');

        $email->attachData(base64_decode($this->pdf), 'Receipt.pdf', ['mime' => 'application/pdf']);

        return $email;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
