<?php

namespace App\Jobs;

use App\Models\Restaurant;
use App\Notifications\RestaurantCreatedNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class NewRestaurantNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Restaurant $restaurant;
    protected string $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Restaurant $restaurant, $token)
    {
        $this->restaurant = $restaurant;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::where('restaurant_id', $this->restaurant->id)->whereHas('roles', function ($query) {
            return $query->where('name', 'manager');
        })->first();

        $data['token'] = $this->token;
        $data['email'] = $user->email;
        $data['type'] = 'restaurant';

        $user->notify(new RestaurantCreatedNotification($this->restaurant, $data));
    }
}
