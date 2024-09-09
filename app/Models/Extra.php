<?php

namespace App\Models;

use App\Models\Scopes\RestaurantScope;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Extra extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    const UNAVAILABLE = 'unavailable';
    const AVAILABLE = 'available';

    const STATUSES = [
        self::AVAILABLE,
        self::UNAVAILABLE,
    ];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope());
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function images()
    {
        return $this->hasMany(ExtraImage::class);
    }

    public function itemBundles()
    {
        return $this->morphMany(OrderItemBundle::class, 'entity', 'entity_type', 'entity_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('price', 'order');
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
            get: fn() => $image ? asset('storage/uploads/' . $image->path()) : '/dist/img/product-placeholder.png',
        );
    }

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
}
