<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductImage extends Model
{
    use HasFactory, LogsActivity;

    const LIST = 'list';
    const SINGLE = 'single';

    const TYPES = [
        self::LIST,
        self::SINGLE
    ];

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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function path(): string
    {
         return $this->product->restaurant_id . '/products/images/' . $this->filename;
    }
}
