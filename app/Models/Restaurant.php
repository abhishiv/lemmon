<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendNewOrderNotification;

class Restaurant extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    const BLOCKED = 'blocked';
    const PENDING = 'pending';
    const ACTIVE = 'active';

    const STATUSES = [
        self::BLOCKED,
        self::PENDING,
        self::ACTIVE
    ];
    const CHANGESTATUS = [
        self::BLOCKED,
        self::ACTIVE
    ];

    const RECEIPT = 'receipt';

    const PRINT_TYPE = [
        Product::BAR,
        Product::RESTAURANT,
        self::RECEIPT,
    ];

    protected $casts = [
        'onboarded_at' => 'datetime:Y-m-d',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $guarded = ['id', 'bank_account', 'onboarded_by', 'onboarded_at'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    public function manager(): Attribute
    {
        return new Attribute(
            get: fn() => User::where('restaurant_id', $this->id)->whereHas('roles', function ($query) {
                return $query->where('name', 'manager');
            })->first()
        );
    }

    public function onboarded(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'onboarded_by');
    }

    public function services() : HasMany {
        return $this->hasMany(Service::class);
    }

    /**
     * Get this restaurant's settings
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function settings() {
        return $this->morphMany(RestaurantSetting::class, 'model');
    }

    /**
     * Get this restaurant's product categories
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function productCategories() {
        return $this->hasMany(ProductCategory::class);
    }

    /**
     * Get this restaurant's food types
     *
     * @return HasMany
     */
    public function foodTypes() : HasMany {
        return $this->hasMany(FoodType::class);
    }

    /**
     * Get the first service, as it appears in the customer menu
     *
     * @return App\Models\Service | null
     */
    public function getFirstShownService() {
        return $this->services()->where('status', Service::ACTIVE)->with('products')->get()->filter(function ($service) {
            if($service->products->isEmpty()) return false;
            if(!$service->isAvailable() && $service->hide_unavailable) return false;
            return true;
        })->first();
    }

    /**
     * Get the services as they appear in the customer menu
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getShownServicesAttribute() {
        return $this->services()->where('status', Service::ACTIVE)->with('products')->get()->filter(function ($service) {
            if($service->products->isEmpty()) return false;
            if(!$service->isAvailable() && $service->hide_unavailable) return false;
            return true;
        });
    }

    /**
     * Verify if this current restaurant is in the working hours interval
     *
     * @return [boolean]
     */
    public function getValidWorkingHoursAttribute() {
        $schedule = $this->getWorkingSchedule();

        if($schedule == false) {
            return false;
        }

        if($schedule[0] <= now() && now() <= $schedule[1]) return true;

        return false;
    }

    public function getWorkingSchedule() {
        $restaurantStartTime = $this->settings()->where('key', 'start_time')->first();
        $restaurantEndTime = $this->settings()->where('key', 'end_time')->first();

        $restaurantStartTime = $restaurantStartTime->value;
        $restaurantEndTime = $restaurantEndTime->value;

        if(!$restaurantStartTime || !$restaurantEndTime) return false;

        if(Carbon::createFromFormat('H:i', $restaurantEndTime) < Carbon::createFromFormat('H:i', $restaurantStartTime)) {
            // The end time is less than start time (e.g. 4:00 PM - 4:00 AM) so it spans over 2 calendaristic days

            // Verify if it is created in the 2nd day (12 AM - End time), so the restaurant's schedule started the previous day
            if(now() < Carbon::createFromFormat('H:i', $restaurantEndTime)) {
                $endTime = Carbon::createFromFormat('H:i', $restaurantEndTime);
                $startTime = Carbon::createFromFormat('Y-m-d H:i', now()->subDay()->format('Y-m-d').' '.$restaurantStartTime);
            }
            else {
                // Order was created in the interval [start_time, 12 AM]
                $startTime = Carbon::createFromFormat('H:i', $restaurantStartTime);
                $endTime = Carbon::createFromFormat('Y-m-d H:i', now()->addDay()->format('Y-m-d').' '.$restaurantEndTime);
            }
        }
        else {
            // The restaurant schedule is as normal and spans only over one day (e.g. 10:00 AM - 4:00 PM)
            $startTime = Carbon::createFromFormat('H:i', $restaurantStartTime);
            $endTime = Carbon::createFromFormat('H:i', $restaurantEndTime);
        }

        return [$startTime, $endTime];
    }

    public function generateDefaultFoodTypes() {
        $foodTypes = [
            'Appetizer',
            'Main Course',
            'Dessert'
        ];

        $this->foodTypes()->saveMany(array_map(function ($key) use ($foodTypes) {
            return new FoodType([
                'name' => $foodTypes[$key],
                'order' => $key,
            ]);
        }, array_keys($foodTypes)));
    }

    public function reorderFoodTypes() {
        $currentPos = 0;
        $foodTypes = $this->foodTypes()->orderBy('order', 'ASC')->get();

        foreach($foodTypes as $foodType) {
            $foodType->order = $currentPos++;
            $foodType->save();
        }
    }

    public function getLogo()
    {
        if (!$this->receipt_logo) {
            return false;
        }
        
        $path = $this->id . '/receipt/' . $this->receipt_logo;

        if (Storage::disk('public_uploads')->exists($path)) {
            return [
                'name' => $this->receipt_logo,
                'path' => asset('storage/uploads/' . $path),
                'size' => Storage::disk('public_uploads')->size($path)
            ];
        }

        return false;
    }

    // public function getPrinterLocations()
    // {
    //     $locations = [];
    //     $printers = restaurant_settings_get('printers');

    //     if (is_array($printers)) {
    //         $locations = array_map(function($printer) {
    //             if ($printer->area === 'waiter') {
    //                 return 'all';
    //             }
    //             return $printer->area;
    //         }, $printers);
    //     }
    //     return $locations;
    // }

    public function getAvailablePrinterTypes()
    {
        $types = [];
        $printers = restaurant_settings_get('printers', $this);

        if (is_array($printers)) {
            foreach ($printers as $printer) {
                $types = array_merge($types, explode(',', $printer->print_type));
            }
        }

        $availableTypes = [];

        foreach ($this::PRINT_TYPE as $type) {
            $availableTypes[$type] = in_array($type, $types);
        }

        return $availableTypes;
    }

    public function hasPrinterForType(Array $types)
    {
        $printers = restaurant_settings_get('printers', $this);

        if (!is_array($printers)) {
            return false;
        }

        $availableTypes = [];

        foreach ($printers as $printer) {
            $availableTypes = array_merge($availableTypes, explode(',', $printer->print_type));
        }

        return count(array_intersect($availableTypes, $types)) > 0 ? true : false;
    }

    public function getPrinter($printType)
    {
        $printers = restaurant_settings_get('printers', $this);

        if (!is_array($printers)) {
            return false;
        }

        $eligiblePrinters = array_filter($printers, function($printer) use ($printType) {
            return in_array($printType, explode(',', $printer->print_type));
        });

        $eligiblePrinters = array_values($eligiblePrinters);

        return $eligiblePrinters[0] ?? false;
    }

    public function sentNewOrderNotification()
    {
        $deviceIds = json_decode($this->onesignal_device_ids);

        if (is_array($deviceIds)) {
            foreach ($deviceIds as $deviceId) {
                SendNewOrderNotification::dispatch($deviceId);
            }
        }
    }

    public function getAppWelcomeScreenImage()
    {
        if (!$this->welcome_screen_image) {
            return false;
        }
        
        $path = $this->id . '/images/' . $this->welcome_screen_image;

        if (Storage::disk('public_uploads')->exists($path)) {
            return [
                'name' => $this->welcome_screen_image,
                'path' => asset('storage/uploads/' . $path),
                'size' => Storage::disk('public_uploads')->size($path)
            ];
        }

        return false;
    }

    public function getDeliveryFeeForCity($cityName)
    {
        $cities = restaurant_settings_get('delivery_cities', $this);

        if (is_array($cities)) {
            foreach ($cities as $city) {
                if ($city->name === $cityName) {
                    return $city->fee;
                }
            }
        }
        
        return null;
    }
}
