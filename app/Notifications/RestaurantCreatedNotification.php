<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestaurantCreatedNotification extends Notification
{
    use Queueable;

    protected Restaurant $restaurant;
    protected array $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Restaurant $restaurant, $data)
    {
        $this->restaurant = $restaurant;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->view('emails.restaurant_created',
            ['restaurant' => $this->restaurant, 'data' => $this->data])
            ->subject('Welcome to Lemmon');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            //
        ];
    }
}
