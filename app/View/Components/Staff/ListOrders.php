<?php

namespace App\View\Components\Staff;

use App\Models\Restaurant;
use Illuminate\View\Component;

class ListOrders extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $orders;
    public $grouped;

    public $foodTypes;

    public $restaurant;
    public $availablePrinters;

    public function __construct($orders, $restaurant, $availablePrinters)
    {
    $this->orders = $orders;
    $this->restaurant = $restaurant;
    $this->availablePrinters = $availablePrinters;

    // Food types are already loaded via the controller
    $this->foodTypes = $restaurant->foodTypes->pluck('name', 'id')->all();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.staff.list-orders');
    }
}
