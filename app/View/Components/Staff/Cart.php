<?php

namespace App\View\Components\Staff;

use App\Http\Services\StaffCartService;
use Illuminate\View\Component;

class Cart extends Component
{
    public $cart;
    public $cartOptions;
    public $products;
    public $totals;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $service = new StaffCartService();
        $this->cart = $service->getCart();
        $this->cartOptions = $service->getCartOptions();
        $this->products = $service->getProducts();
        $this->totals = $service->getTotals();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.staff.cart');
    }
}
