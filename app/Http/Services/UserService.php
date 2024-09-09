<?php

namespace App\Http\Services;

use App\Jobs\NewRestaurantNotification;
use App\Jobs\NewStaffNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class UserService
{
    public function store($request): User
    {
        $user = User::create([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'restaurant_id' => $request->restaurant_id,
            'status' => User::PENDING,
            'staff_type' => $request->staff_type ?? null,
            'password' => bcrypt(rand(0, 1000)),
        ]);

        $user->assignRole($request->role);

        if ($request->submit == 'invite' || $request->submit == 'reinvite') {
            $this->firstTimePassword($request, $user);
        }
        $user->tables()->sync($request->tables);

        return $user;
    }

    public function update(User $user, $request): User
    {
        if ($request->submit == 'invite' || $request->submit == 'reinvite') {
            $this->firstTimePassword($request, $user);
        }

        $validated = $request->validated();

        if(array_key_exists('contact_person', $validated)) {
            $validated['name'] = $validated['contact_person'];
        }

        $user->update($validated);

        $user->tables()->sync($request->tables);

        return $user;
    }

    public function destroy(User $user): void
    {
        $user->delete();
    }

    private function firstTimePassword($request, $user = []): void
    {
        $token = Str::random(64);

        DB::table('password_resets')->where('email', $request->email)->delete();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => bcrypt($token),
            'created_at' => Carbon::now()
        ]);

        if ($request->role == User::STAFF) {
            NewStaffNotification::dispatch($user, $token);
        } else {
            NewRestaurantNotification::dispatch($request->restaurant, $token);
        }
    }

    public function changeStatus(User $user, $request): bool
    {
        if($request['status'] === User::OUTOFOFFICE || $request['status'] === User::ACTIVE){

            $user->update(['status' => $request['status']]);

            return true;
        }

        return false;

    }

}
