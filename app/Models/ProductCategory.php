<?php

namespace App\Models;

use App\Models\Scopes\RestaurantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    const STATUSES = [
        self::ACTIVE,
        self::INACTIVE
    ];

    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope());
        static::deleted(function () {
            $currentOrder = 0;
            $categories = ProductCategory::orderBy('order', 'ASC')->get();
            foreach ($categories as $category) {
                $category->update([
                    'order' => ++$currentOrder,
                ]);
            }
        });
        static::created(function () {
            $currentOrder = 0;
            $categories = ProductCategory::orderBy('order', 'ASC')->get();
            foreach ($categories as $category) {
                $category->update([
                    'order' => ++$currentOrder,
                ]);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    public function products(): belongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_to_category', 'category_id',
            'product_id')->whereNot('status',
            Product::UNAVAILABLE)->withPivot('order')->orderBy('pivot_order')->withTimestamps();
    }

    public function hasAvailableProducts(): bool
    {
        $count = 0;
        foreach ($this->products as $product) {
            if ($product->serviceHideProduct()) {
                $count++;
            }
        }
        if ($count == $this->products->count()) {
            return false;
        }
        return true;
    }
}
