<?php

namespace App\View\Components\Customer;

use Illuminate\View\Component;

class ShoppingCart extends Component
{
    public $totalItems = 0;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (session()->has('cart')) {
            $this->totalItems =  array_sum(array_column(session('cart'), 'quantity'));
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.customer.shopping-cart');
    }
}
