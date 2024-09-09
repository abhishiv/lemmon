<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class OrderStatusNotification extends Notification
{
    use Queueable;

    protected string $deviceId;
    protected $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($deviceId, $order)
    {
        $this->deviceId = $deviceId;
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [OneSignalChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return OneSignalMessage
     */
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject("Order #" . $this->order->display_id)
            ->setBody('Your order is ready!')
            ->setUrl(route('customer.order.list'));

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

    public function routeNotificationForOneSignal(){
        return $this->deviceId;
    }
}
