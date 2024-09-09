<?php

namespace App\View\Components\Customer;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OrderCard extends Component
{
    public array $orders = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {

        if (!session()->has('orders')) {
            return;
        }

        $sessionOrders = session('orders');

        if (empty($sessionOrders)) {
            return;
        }

        session()->forget('orders');

        foreach ($sessionOrders as $orderId) {
            $order = Order::where('id', $orderId)->whereIn('status', Order::STAFFSTATUS)->get()->first();
            if ($order) {
                $this->orders[] = $order;
            }
        }

        if (empty($this->orders)) {
            session()->put('orders', []);
            return;
        }

        foreach ($this->orders as $order) {
            session()->put('orders.' . $order->id, $order->id);
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|\Closure|string
     */
    public function render(): View|string|\Closure
    {
        return view('components.customer.order-card');
    }
}
