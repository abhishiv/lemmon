<?php

namespace App\Models;

use App\Models\Scopes\RestaurantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope());
    }

    public function products() : HasMany {
        return $this->hasMany(Product::class);
    }

    public function restaurant() : BelongsTo {
        return $this->belongsTo(Restaurant::class);
    }
}
