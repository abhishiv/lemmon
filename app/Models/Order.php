<?php

namespace App\Models;

use App\Models\Scopes\RestaurantScope;
use App\Payment\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope());
    }

    const ONLINE = 'online';
    const CASH = 'cash';
    const CARD = 'card';

    const INITIAL = 'initial';    //Payment process started
    const FAILED = 'failed';     //Payment failed
    const NEW = 'new';       //Success payment
    const PREPARING = 'preparing';  //Preparing order
    const READY = 'ready';      //Order is ready for pickup or serve
    const CLOSED = 'closed';     //Order is served
    const CANCELED = 'canceled'; //Order has been cancelled by staff
    const GROUP = 'group'; //Order is waiting for group

    const PRINTED = 'printed'; // Job printed
    const PENDING = 'pending'; // Job sent to printer
    const NOTPRINTED = 'not-printed'; // Job was sent to printer and failed
    const NOTHINGTOPRINT = 'nothing-to-print'; // Job was sent to printer and failed

    const DINEIN = 'dine-in';
    const TAKEAWAY = 'takeaway';
    const DELIVERY = 'delivery';

    const STAFFSTATUS = [
        self::PREPARING,
        self::READY,
        self::NEW,
        self::GROUP,
    ];
    const CHOOSESTATUS = [
        self::NEW,
        self::PREPARING,
        self::READY,
        self::CLOSED,
    ];
    const CHOOSESTATUSGROUPED = [
        self::PREPARING,
        self::READY,
        self::CLOSED,
    ];

    const PRINTSTATUS = [
        self::PRINTED,
        self::PENDING,
        self::NOTPRINTED,
        self::NOTHINGTOPRINT,
    ];

    const NOPRINTING = [
        self::PRINTED,
        self::NOTHINGTOPRINT,
    ];

    const SERVICEMETHODS  = [
        self::DINEIN => 'Dine In',
        self::TAKEAWAY => 'Takeaway',
        self::DELIVERY => 'Delivery',
    ];

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    public function parent() : BelongsTo {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children() : HasMany {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function items(): hasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function restaurant(): belongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table(): belongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function payments(): hasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function foodStatuses() : HasMany {
        return $this->hasMany(OrderFoodStatus::class);
    }

    public function events() : HasMany {
        return $this->hasMany(OrderEvent::class);
    }

    public function deliveryInformation() : HasOne
    {
        return $this->hasOne(DeliveryInformation::class);
    }

    public function pickupDetails() : HasOne
    {
        return $this->hasOne(PickupDetails::class);
    }

    public function partialPayments(): BelongsToMany
    {
        return $this->belongsToMany(PartialPayment::class, 'order_partial_payment', 'order_id', 'payment_id');
    }

    public function discounts() : HasMany
    {
        return $this->hasMany(AppliedDiscount::class);
    }

    public function getItems() {
        return Product::with('items.order')
            ->whereHas('items.order', function ($query) {
                $query->where('parent_id', $this->id);
            })
            ->get();
    }

    public function foodTypeCount(): Attribute
    {
        $count = 0;

        // Verify if this is a parent order
        if($this->isParent) {
            $count = Product::with('items.order')
                ->whereHas('items.order', function ($query) {
                    $query->where('parent_id', $this->id);
                })
                ->where('type', Product::RESTAURANT)->count();
        } else {
            foreach ($this->items as $item) {
                if ($item->products && $item->products->type == Product::RESTAURANT) {
                    $count++;
                }
            }
        }

        return new Attribute(
            get: fn() => $count
        );
    }

    public function barTypeCount(): Attribute
    {
        $count = 0;

        if($this->isParent) {
            $count = Product::with('items.order')
                ->whereHas('items.order', function ($query) {
                    $query->where('parent_id', $this->id);
                })
                ->where('type', Product::BAR)->count();
        } else {
            foreach ($this->items as $item) {
                if ($item->products && $item->products->type == Product::BAR) {
                    $count++;
                }
            }
        }

        return new Attribute(
            get: fn() => $count
        );
    }

    public function orderProducts(): Attribute
    {
        $products = [];

        foreach ($this->items as $item) {
            $products[] = $item->products;
        }

        return new Attribute(
            get: fn() => $products
        );
    }

    public function fakeId(): Attribute
    {
        return new Attribute(
            get: fn() => $this->id + 1000
        );
    }

    public function isParent(): Attribute
    {
        return new Attribute(
            get: fn() => $this->is_grouped && is_null($this->parent_id),
        );
    }

    public function getTotalAmountAttribute() {
        if($this->isParent) {
            $total  = 0;

            $children = $this->children()
                ->where(function ($query) {
                    $query->whereIn('payment_method', [Order::CASH, Order::CARD])
                        ->orWhereNull('payment_method');
                })
                ->get();

            foreach($children as $child) {
                $total += $child->totalAmount;
            }

            return $total;
        }

        $total = $this->amount;

        if($this->tips) {
            $total += $this->tips;
        }

        if($this->delivery_fee) {
            $total += $this->delivery_fee;
        }

        return $total;
    }

    public function getTotalTipsAttribute() {
        if($this->isParent) {
            $totalTips  = 0;

            foreach($this->children()->get() as $child) {
                $totalTips += $child->tips;
            }

            return $totalTips;
        }

        return $this->tips;
    }

    public function getFoodByTypesAttribute() {
        return array_reduce($this->items()->with('products.foodType')->whereHas('products', function ($query) {
                $query->whereNotNull('food_type_id');
            })->get()->all(), function ($carry, $item) {

                if($item->products->foodType) {
                    $carry[$item->products->food_type_id][] = $item;
                }

            return $carry;
        }, []);
    }

    /**
     * Generate a new display ID for this order based on the previous orders' in the same schedule day
     * @return int
     */
    public function generateDisplayId() {
        $restaurant_start_time = restaurant_settings_get('start_time', $this->restaurant);
        $restaurant_end_time =  restaurant_settings_get('end_time', $this->restaurant);

        if(Carbon::createFromFormat('H:i', $restaurant_end_time) < Carbon::createFromFormat('H:i', $restaurant_start_time)) {
            // The end time is less than start time (e.g. 4:00 PM - 4:00 AM) so it spans over 2 calendaristic days

            // Verify if it is created in the 2nd day (12 AM - End time), so the restaurant's schedule started the previous day
            if(now() < Carbon::createFromFormat('H:i', $restaurant_end_time)) {
                $end_time = Carbon::createFromFormat('H:i', $restaurant_end_time);
                $start_time = Carbon::createFromFormat('Y-m-d H:i', now()->subDay()->format('Y-m-d').' '.$restaurant_start_time);
            }
            else {
                // Order was created in the interval [start_time, 12 AM]
                $start_time = Carbon::createFromFormat('H:i', $restaurant_start_time);
                $end_time = Carbon::createFromFormat('Y-m-d H:i', now()->addDay()->format('Y-m-d').' '.$restaurant_end_time);
            }
        }
        else {
            // The restaurant schedule is as normal and spans only over one day (e.g. 10:00 AM - 4:00 PM)
            $start_time = Carbon::createFromFormat('H:i', $restaurant_start_time);
            $end_time = Carbon::createFromFormat('H:i', $restaurant_end_time);
        }

        $previousDisplayId = Order::where('restaurant_id', $this->restaurant_id)->whereBetween('created_at', [$start_time, $end_time])->max('display_id');
        if(!$previousDisplayId) $previousDisplayId = 0;

        $previousDisplayId += 1;

        $this->display_id = $previousDisplayId;
        $this->saveQuietly();

        return $previousDisplayId;
    }

    public function getDisplayId() {
        return $this->parent_id ? $this->parent->display_id : $this->display_id;
    }

    public function getNextStatusButtonText($type) {
        if($type == 'restaurant') {
            $status = $this->restaurant_status;
        }

        if($type == 'bar') {
            $status = $this->bar_status;
        }

        if($status == static::GROUP) {
            return __('labels.status-group');
        }

        if($status == static::NEW) {
            return __('labels.status-new');
        }

        if($status == static::PREPARING) {
            return __('labels.status-preparing');
        }

        if($status == static::READY) {
            return __('labels.status-ready');
        }
    }

    private function generateInitialFoodStatuses($forceOverwrite) {
        if($this->foodStatuses()->count() && !$forceOverwrite) {
            return;
        }

        if($forceOverwrite) {
            $this->foodStatuses()->delete();
        }

        $foodTypes = $this->foodByTypes;

        if(empty($foodTypes)) {
            return;
        }

        $foodStatuses = [];

        $status = $forceOverwrite ? $this->restaurant_status : (($this->restaurant_status == Order::PREPARING && $this->restaurant->hasPrinterForType([Product::RESTAURANT])) ? OrderFoodStatus::PREPARING : OrderFoodStatus::NEW);

        foreach($foodTypes as $foodTypeId => $items) {
            $foodStatus = new OrderFoodStatus;

            $foodStatus->food_type_id = $foodTypeId;
            $foodStatus->status = $status;

            $foodStatuses[] = $foodStatus;
        }

        $this->foodStatuses()->saveMany($foodStatuses);
    }

    // Status reloads/refreshes
    public function reloadStatuses($forceOverwrite = false) {
        $this->generateInitialFoodStatuses($forceOverwrite);

        if($this->parent) {
            $this->parent->reloadParentStatuses();
        }
    }

    private function reloadParentStatuses(): void
    {
        foreach($this->children as $childOrder) {
            $foodTypes = $childOrder->foodByTypes;

            if(empty($foodTypes)) {
                continue;
            }

            foreach ($foodTypes as $foodTypeId => $items) {

                $existingOrderFoodStatus = OrderFoodStatus::where([
                    'food_type_id' => $foodTypeId,
                    'order_id' => $this->id
                ])->first();

                if (!$existingOrderFoodStatus) {
                    OrderFoodStatus::create([
                        'food_type_id' => $foodTypeId,
                        'order_id' => $this->id,
                        'status' => $this->restaurant_status
                    ]);
                }
            }
        }
    }

    public function refreshStatuses(): void
    {
        if($this->foodTypeCount) {
            // Refresh the food status
            $totalFoodStatuses = $this->foodStatuses()->count();

            if($this->foodStatuses()->where('status', OrderFoodStatus::READY)->count() == $totalFoodStatuses) {
                // All food types are ready
                $newOrderFoodStatus = Order::READY;
            } else if($this->foodStatuses()->where('status', OrderFoodStatus::NEW)->count() == $totalFoodStatuses) {
                // All food types are new
                $newOrderFoodStatus = Order::NEW;
            } else {
                // The food is currently preparing
                $newOrderFoodStatus = Order::PREPARING;
            }

            $this->restaurant_status = $newOrderFoodStatus;
        } // If order has no food, mirror the bar status
        else {
            $this->restaurant_status = $this->bar_status;
        }

        // If order has no bars, mirror the food status
        if(!$this->barTypeCount) {
            $this->bar_status = $this->restaurant_status;
        }

        if($this->restaurant_status == Order::PREPARING || $this->bar_status == Order::PREPARING) {
            // One of them in preparing
            $this->status = Order::PREPARING;
        }
        if($this->restaurant_status == Order::NEW && $this->bar_status == Order::NEW) {
            // Both of them new
            $this->status = Order::NEW;
        }
        if($this->restaurant_status == Order::READY && $this->bar_status == Order::READY) {
            $this->status = Order::READY;
        }

        $this->save();

        if($this->isParent) {
            $this->refreshChildrenStatuses();
        }
    }

    private function refreshChildrenStatuses() {
        $foodStatuses = $this->foodStatuses;
        $barStatus = $this->bar_status;

        foreach($this->children()->with('foodStatuses')->get() as $childOrder) {

            $childOrder->bar_status = $barStatus;

            foreach($foodStatuses as $foodStatus) {
                $childFoodStatus = $childOrder->foodStatuses->where('food_type_id', $foodStatus->food_type_id)->first();

                if($childFoodStatus) {
                    $childFoodStatus->status = $foodStatus->status;
                    $childFoodStatus->save();
                }
            }

            $childOrder->refreshStatuses();
        }
    }

    public function isLater()
    {
        if (!$this->pickupDetails) {
            return false;
        }

        if($this->pickupDetails->day && empty($this->pickupDetails->time)){
            return false;
        }

        $pickupTime = Carbon::createFromFormat('Y-m-d H:i:s', $this->pickupDetails->day . ' ' . $this->pickupDetails->time ?? '');

        return $pickupTime->gt(Carbon::now()->addMinutes(30));
    }

    public function needsPayment()
    {
        // Orders placed by the staff that are dine-in are paid when an order is closed
        // Takeaway orders placed by the staff are paid on the spot (when placed)
        if ($this->user_id) {
            return $this->service_method === Order::DINEIN;
        }

        // Only cash orders placed from the Client App need payment
        return $this->payment_method === Order::CASH;
    }
}
