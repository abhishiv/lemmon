<?php

namespace App\Models;

use App\Models\Scopes\RestaurantScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RestaurantTable extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope);
    }

    const UNAVAILABLE = 'unavailable';
    const AVAILABLE = 'available';

    const SERVE = 'table-service';
    const OFFSITE = 'offsite-service';

    const TYPES = [
        self::SERVE,
        self::OFFSITE,
    ];

    const STATUSES = [
        self::AVAILABLE,
        self::UNAVAILABLE
    ];

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    public function restaurant() {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): hasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    protected function menuUrl(): Attribute
    {
        $restaurant = Restaurant::find($this->restaurant_id);

        return Attribute::make(
          get: fn() => route('customer.menu', [$restaurant->slug, $this->hash]),
        );
    }

    protected function codeUrl(): Attribute
    {
        return Attribute::make(
            get: fn() =>  Storage::disk('public_uploads')->url($this->restaurant_id . '/codes/' . $this->id . '/' . $this->hash . '.png'),
        );
    }
    protected function codePath(): Attribute
    {
        return Attribute::make(get: fn() => Storage::disk('public_uploads')->path("$this->restaurant_id/codes/$this->id/$this->hash.png")
        );
    }

    public function activeOrders()
    {
        return $this->orders
            ->whereNull('parent_id')
            ->whereNotIn('status', [Order::CLOSED, Order::CANCELED, Order::INITIAL, Order::FAILED]);
    }
}
