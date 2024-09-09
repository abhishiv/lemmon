<?php

namespace App\View\Components\Staff;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Order;

class OrderCard extends Component
{
    public $order;

    public $foodItems;
    public $barItems;

    public $foodTypes;

    public $itemsByFoodType;

    public $foodStatuses;

    // Grouped order
    public $groupedFoodItems = [];
    public $groupedBarItems = [];

    public $availablePrinters;

    public $filteringClasses;

    public $hasOnlinePayment;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($order, $foodTypes, $availablePrinters)
    {
        $this->order = $order;

        $this->foodTypes = $foodTypes;

        $this->availablePrinters = $availablePrinters;

        $this->foodStatuses = $order->foodStatuses()->pluck('status', 'food_type_id')->all();

        if($order->isParent) {
            $this->getItemsGroupedOrder();
        } else {
            $this->getItemsOrder();
        }

        $this->filteringClasses = $this->getFilteringClasses();

        $this->hasOnlinePayment = $order->isParent && $order->children()->where('payment_method', \App\Models\Order::ONLINE)->count();
    }

    /**
     * Get the items for a normal, ungrouped order
     *
     * @return void
     */
    private function getItemsOrder() {

        $this->foodItems = $this->order->items()->whereRelation('products', 'type', Product::RESTAURANT)->get();
        $this->barItems = $this->order->items()->whereRelation('products', 'type', Product::BAR)->get();

        $this->itemsByFoodType = $this->order->foodByTypes;
    }

    /**
     * Get the items for a grouped order
     *
     * @return void
     */
    private function getItemsGroupedOrder() {
        $this->getFoodItemsGroupedOrder();
        $this->getBarItemsGroupedOrder();
    }

    private function getFoodItemsGroupedOrder() {
        $foodItemsByType = [];

        $items = OrderItem::with(['products', 'order', 'itemBundles', 'itemBundles.entity'])
            ->whereRelation('products', 'type', Product::RESTAURANT)
            ->whereRelation('order', 'parent_id', $this->order->id)
            ->get();

        foreach($items as $item) {
            $foodTypeId = $item->products->food_type_id;
            if($item->itemBundles->count() > 0) {
                $foodTypeId .= '_' . $item->itemBundles->first()->entity_id;
            }

            if(isset($foodItemsByType[$foodTypeId])) {
                $existingKey = null;
                foreach($foodItemsByType[$foodTypeId] as $key => $foodItem) {
                    if($foodItem['product_id'] == $item->product_id && $foodItem['notes'] == $item->notes && $foodItem['bundle']->pluck('entity_id', 'entity_type') == $item->itemBundles->pluck('entity_id', 'entity_type')) {
                        $existingKey = $key;
                        break;
                    }
                }

                if($existingKey !== null) {
                    $foodItemsByType[$foodTypeId][$existingKey]['quantity'] += $item->quantity;
                    continue;
                }
            }

            $foodItemsByType[$item->products->food_type_id][] = [
                'product_id' => $item->product_id,
                'name' => $item->products->name,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
                'bundle' => $item->itemBundles
            ];


        }

        $this->groupedFoodItems = $foodItemsByType;
    }

    private function getBarItemsGroupedOrder() {
        $barItemsByType = [];

        $items = OrderItem::with(['products', 'order', 'itemBundles',  'itemBundles.entity'])
            ->whereRelation('products', 'type', Product::BAR)
            ->whereRelation('order', 'parent_id', $this->order->id)
            ->get();

        foreach ($items as $item) {
            $foodTypeId = $item->id;

            if ($item->itemBundles->count() > 0) {
                $foodTypeId .= '_' . $item->itemBundles->first()->entity_id;
            }

            if (isset($barItemsByType[$foodTypeId])) {
                $existingKey = null;
                foreach ($barItemsByType[$foodTypeId] as $key => $barItem) {
                    if ($barItem['product_id'] == $item->product_id && $barItem['notes'] == $item->notes && $barItem['bundle']->pluck('entity_id', 'entity_type') == $item->itemBundles->pluck('entity_id', 'entity_type')) {
                        $existingKey = $key;
                        break;
                    }
                }

                if ($existingKey !== null) {
                    $barItemsByType[$foodTypeId][$existingKey]['quantity'] += $item->quantity;
                    continue;
                }
            }

            $barItemsByType[$foodTypeId] = [
                'product_id' => $item->product_id,
                'name' => $item->products->name,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
                'bundle' => $item->itemBundles
            ];
        }
        $this->groupedBarItems = $barItemsByType;
    }

    private function getFilteringClasses()
    {
        $classes = [];

        $classes[] = 'order-card--status-' . $this->order->status;

        $classes[] = 'order-card--' . $this->order->service_method;

        $classes[] = 'order-card--' . ($this->order->isParent ? 'grouped' : 'stand-alone');

        $classes[] = 'order-card--' . ($this->order->isLater() ? 'later' : 'now');

        if (in_array($this->order->bar_status, [Order::NEW, Order::PREPARING])) {
            $hasBarItems = false;

            if($this->order->isParent && count($this->groupedBarItems)) {
                $hasBarItems = true;
            } else if ($this->barItems?->count()) {
                $hasBarItems = true;
            }

            if ($hasBarItems) {
                $classes[] = 'order-card--visible-bar';
            }
        }

        if (count(array_intersect(array_values($this->foodStatuses), [Order::NEW, Order::PREPARING]))) {
            $classes[] = 'order-card--visible-restaurant';
        }

        return implode(' ', $classes);
    }


    /**
     * Get the view / contents that represent the component.
     *
     * @return View|\Closure|string
     */
    public function render(): View|string|\Closure
    {
        return view('components.staff.order-card');
    }
}
