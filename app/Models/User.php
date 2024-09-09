<?php

namespace App\Models;

use App\Models\Scopes\RestaurantScope;
use App\Notifications\PasswordReset;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, CausesActivity;

    const BLOCKED = 'blocked';
    const PENDING = 'pending';
    const ACTIVE = 'active';
    const OUTOFOFFICE = 'out_of_office';

    const STATUSES = [
        self::BLOCKED,
        self::PENDING,
        self::ACTIVE,
        self::OUTOFOFFICE
    ];

    const ADMIN = 'admin';
    const MANAGER = 'manager';
    const STAFF = 'staff';

    const KITCHEN = 'kitchen';
    const WAITER = 'waiter';
    const BARTENDER = 'bar';

    const STAFFTYPES = [
        self::KITCHEN,
        self::WAITER,
        self::BARTENDER,
    ];

    public function scopeRestaurant($query)
    {
        return $query->where('restaurant_id', auth()->user()->restaurant_id);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $guarded = ['id', 'email_verified_at', 'remember_token', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];

    public function initials(): Attribute
    {
        $words = explode(" ", $this->name);
        $initials = null;
        foreach ($words as $ini){
            if (!empty($ini)){
                $initials .= $ini[0];
            }
        }

        return new Attribute(
            get: fn() => $initials
        );
    }

    public function tables(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_table_to_user', 'user_id', 'table_id',);
    }

    public function restaurantJobs() {
        return $this->hasMany(RestaurantJob::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token));
    }
}
