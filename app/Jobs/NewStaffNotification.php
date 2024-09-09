<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\StaffCreatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class NewStaffNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $token;
    protected User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle($this->user->id)->allow(2)->every(1)->then(function () {
            $data['token'] = $this->token;
            $data['email'] = $this->user->email;
            $data['type'] = 'staff';

            $this->user->notify(new StaffCreatedNotification($this->user, $data));
        });

    }
}
