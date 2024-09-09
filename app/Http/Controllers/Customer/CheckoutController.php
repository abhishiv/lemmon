<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\CartService;
use App\Http\Services\CheckoutService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService, protected CartService $cartService)
    {
    }

    public function list(): View|Factory|Application|RedirectResponse
    {
        if (empty(session('cart'))) {
            return Redirect::to(session('table.url'));
        }

        $products = $this->cartService->products();

        $notes = $this->cartService->notes();

        $total = $this->cartService->total();

        return view('customer.checkout', compact('products', 'total', 'notes'));
    }
}
