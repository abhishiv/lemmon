<?php

namespace App\View\Components\customer;

use Illuminate\View\Component;

class OrderSummary extends Component
{
    public $orders;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.customer.order-summary');
    }
}
