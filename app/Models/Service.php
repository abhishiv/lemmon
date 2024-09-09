<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\ProductCategory;
use Spatie\Activitylog\LogOptions;
use App\Models\Scopes\RestaurantScope;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope());
    }

    const WEEKDAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        0 => 'Sunday',
    ];

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    const SERVE = 'table-service';
    const OFFSITE = 'offsite-service';

    const TYPES = [
        self::SERVE,
        self::OFFSITE,
    ];

    const statuses = [
        self::ACTIVE,
        self::INACTIVE
    ];

    protected $casts = [
        'days' => 'array',
    ];

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'service_to_service_type')->withTimestamps();;
    }

    /**
     * Get the products of this service by each category
     *
     * @param boolean $hideDisabled
     * @return array
     */
    public function productsByCategories($hideDisabled = false): array
    {
        $groupedProducts = [];

        foreach ($this->products as $product) {
            if(!$product->serviceHideProduct()) {
                $categories = $product->categories;

                if ($hideDisabled) {
                    $categories = $categories->filter(function ($category) {
                        return $category->status == ProductCategory::ACTIVE;
                    });
                }

                foreach ($categories as $cat) {
                    $groupedProducts[$cat->id]['products'][] = $product;
                    $groupedProducts[$cat->id]['category'] = $cat;
                }
            }
        }
        return $groupedProducts;
    }

    /**
     * Get the services as they appear in the customer menu
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getShownAttribute(): Illuminate\Database\Eloquent\Collection|bool
    {
        if ($this->products->isNotEmpty()) {
            return false;
        }

        if (!$this->isAvailable() && $this->hide_unavailable) {
            return false;
        }

        return true;

    }

    public function isAvailable($includeVisibleOnlyToStaff = false): bool
    {
        if ($this->status == self::INACTIVE) {
            return false;
        }

        if (!$includeVisibleOnlyToStaff && $this->visible_only_to_staff) {
            return false;
        }

        if ($this->days) {
            $today = Carbon::now()->shortEnglishDayOfWeek;

            if (isset($this->days[$today])) {
                $now = Carbon::now();

                foreach ($this->days[$today] as $timeRange) {
                    $start = Carbon::parse($timeRange['start']);
                    $end = Carbon::parse($timeRange['end']);

                    if ($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }


    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_services', 'service_id',
            'product_id')->withTimestamps()->whereNot('status',
            Product::UNAVAILABLE)->with(['categories'])->withPivot('order')->orderBy('order');
    }
}
