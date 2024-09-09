<?php

namespace App\Models;

use App\Models\Scopes\RestaurantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Menu extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope());
    }

    protected $guarded = [];

    protected $with = [
        'items'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('menu_order', 'ASC');
    }

    public function productcategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'menu_items');
    }
}
