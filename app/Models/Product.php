<?php

namespace App\Models;

use App\Models\Scopes\RestaurantScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Http\Services\RestaurantSettingSingleton;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope());
    }

    const UNAVAILABLE = 'unavailable';
    const AVAILABLE = 'available';
    const OUTOFSTOCK = 'out_of_stock';

    const RESTAURANT = 'restaurant';
    const BAR = 'bar';

    const STATUSES = [
        self::AVAILABLE,
        self::UNAVAILABLE,
        self::OUTOFSTOCK,
    ];

    const TYPES = [
        self::RESTAURANT,
        self::BAR
    ];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_to_category', 'product_id',
            'category_id')->withTrashed()->withPivot('order')->orderBy('pivot_order')->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function foodType(): BelongsTo
    {
        return $this->belongsTo(FoodType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(Bundle::class);
    }

    public function removables(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class, 'removable_products', 'product_id', 'extra_id')->withPivot(
            'order')->orderBy('pivot_order')->withTimestamps();
    }

    public function extraProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'products_extra_products', 'product_id', 'extra_product_id')->withPivot('price',
            'order')->orderBy('pivot_order')->withTimestamps();
    }

    public function services($unavailable = true): belongsToMany
    {
        $relation = $this->belongsToMany(Service::class, 'product_services', 'product_id',
            'service_id')->withPivot('order')->orderBy('pivot_order')->withTimestamps();

        if (!$unavailable) {
            $relation->where('status', Service::ACTIVE);
        }

        return $relation;
    }

    public function availableService(): bool|Service
    {
        if ($this->services->isEmpty()) {
            return true;
        }
        foreach ($this->services as $service) {
            if ($service->isAvailable(true)) {
                return $service;
            }
        }
        return false;
    }

    public function serviceHideProduct(): bool
    {

        if ($this->services->isEmpty() || $this->availableService()) {
            return false;
        }

        foreach ($this->services as $service) {
            if (!$service->hide_unavailable) {
                return false;
            }
        }

        return true;
    }

    public function bundle()
    {
        return $this->hasOne(Bundle::class);
    }

    public function related(): belongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id');
    }

    public function extras(): belongsToMany
    {
        return $this->belongsToMany(Extra::class, 'product_extras', 'product_id', 'extra_id')->withPivot(['price', 'order'])->orderBy('pivot_order')->withTimestamps();
    }

    public function itemBundles(): MorphMany
    {
        return $this->morphMany(OrderItemBundle::class, 'entity', 'entity_type', 'entity_id');
    }

    public function salePrice(): Attribute
    {
        return new Attribute(
            get: fn() => $this->special_price ?: $this->price,
        );
    }

    public function featuredImage(): Attribute
    {
        $image = $this->images->first(function ($image) {
            return $image->type === 'list';
        });

        return new Attribute(
            get: fn() => $image ? asset('storage/uploads/' . $image->path()) : false,
        );

    }

    public function singleImage(): Attribute
    {
        $image = $this->images()->where('type', 'single')->first();

        return new Attribute(
            get: fn() => $image ? asset('storage/uploads/' . $image->path()) : '',
        );
    }

    /**
     * Return a gallery of images
     * @return array
     */
    public function getImages(): array
    {
        $gallery = [];

        foreach ($this->images as $image) {
            $path = $image->path();

            if (Storage::disk('public_uploads')->exists($path)) {
                $gallery[$image->id]['id'] = $image->id;
                $gallery[$image->id]['name'] = $image->filename;
                $gallery[$image->id]['type'] = $image->type;
                $gallery[$image->id]['path'] = asset('storage/uploads/' . $path);
                $gallery[$image->id]['size'] = Storage::disk('public_uploads')->size($path);
            }
        }

        return $gallery;
    }

    /**
     * Return menu URL for the product
     */
    public function menuProductUrl(): Attribute
    {
        return new Attribute(
            get: fn() => route('customer.product.show', [session('restaurant.slug'), $this->slug]),
        );
    }
}
