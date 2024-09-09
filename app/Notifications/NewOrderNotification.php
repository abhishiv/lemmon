<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    protected string $deviceId;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($deviceId)
    {
        $this->deviceId = $deviceId;
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
            ->setSubject("You have a new order.")
            ->setBody('A new order has been registered.')
            ->setUrl(route('staff.dashboard'));

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
