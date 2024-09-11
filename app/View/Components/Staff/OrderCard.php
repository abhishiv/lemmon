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

        // Instead of querying the foodStatuses, use preloaded data
        $this->foodStatuses = $order->foodStatuses->pluck('status', 'food_type_id')->all();

        // Use the preloaded items instead of querying again
        if($order->isParent) {
            $this->getItemsGroupedOrder($order->items);
        } else {
            $this->getItemsOrder($order->items);
        }
        $this->items = $order->items;

        $this->filteringClasses = $this->getFilteringClasses();
        $this->hasOnlinePayment = $order->isParent && $order->children->where('payment_method', \App\Models\Order::ONLINE)->count();
    }

    private function getItemsOrder($items)
    {
        // Filter the items by product type directly from the preloaded data
        $this->foodItems = $items->filter(fn($item) => $item->products->type === Product::RESTAURANT);
        $this->barItems = $items->filter(fn($item) => $item->products->type === Product::BAR);
        $this->itemsByFoodType = $this->order->foodByTypes;
    }

    private function getItemsGroupedOrder($items)
    {
        // Similar optimization for grouped items
        $this->groupedFoodItems = $this->groupItemsByType($items, Product::RESTAURANT);
        $this->groupedBarItems = $this->groupItemsByType($items, Product::BAR);
    }

    private function groupItemsByType($items, $type)
    {
        return $items->filter(fn($item) => $item->products->type === $type)->groupBy(fn($item) => $item->products->food_type_id);
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
