<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderItem extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    const PAID = 'paid';

    const STATUSES = [
        self::PAID,
    ];

    protected $guarded = [];

    protected $with = [
        'products',
        'itemBundles',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    public function order() : BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function products(): belongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    public function itemBundles(): HasMany
    {
        return $this->hasMany(OrderItemBundle::class, 'order_item_id');
    }
}
