<?php

namespace App\Http\Controllers\Customer;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use App\Http\Services\OrderService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Foundation\Application;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
    }

    /**
     * Store the order and order items and start payment
     *
     * @param StoreOrderRequest $request
     * @return Application|Redirector|RedirectResponse
     */
    public function store(StoreOrderRequest $request): Redirector|RedirectResponse|Application
    {
        try {
            $order = $this->orderService->store($request);

            if($order === 'unavailable') {
                return back()->with(['error' => __('customer.table-unavailable-error')]);
            }

            $redirectUrl = $this->orderService->pay($order);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            if(getEnv('APP_ENV') == 'staging') {
                dd($e);
            }
            return back()->with(['error' => __('customer.payment-message-error')]);
        }

        return redirect($redirectUrl);

    }

    public function list()
    {
        $response = $this->orderService->checkOrderExists();

        if ($response['url']) {
            //If the order is completed and user was in the menu page and clicked the order list button, he will be redirected back to the menu.
            if(str_contains(request()->headers->get('referer'), 'table')){
                return redirect(request()->headers->get('referer'));
            }
            return redirect($response['url']);
        }

        return view('customer.orders.list', ["orders" => $response['orders'], 'order' => $response['orders']->last()]);
    }

    public function get()
    {
        $response = $this->orderService->checkOrderExists();

        if ($response['url']) {
            return response()->json(['url' => $response['url']]);
        }

        return response()->json(['status' => $response['orders']->last()->status, 'canceled' => $response['canceled']])->header('Cache-Control', 'no-cache');
    }


    public function receipt(Request $request)
    {
        $request->validate([
            'email' => ['required_if:type,email', 'nullable', 'email'],
            'type'  => ['required', 'string']
        ]);
        $pdf = $this->orderService->sendReceipt($request->email);

        $hiddenEmail = null;

        if($request->email) {
            [$username, $domain] = explode('@', $request->email);
            $firstTwoChars = substr($username, 0, 2);
            $lastChar = substr($username, -1);
            $hiddenUsername = $firstTwoChars . str_repeat('*', strlen($username) - 3) . $lastChar;
            $hiddenEmail = $hiddenUsername . '@' . $domain;
        }

        DB::table('receipt_requests')->insert(
            [
                'restaurant_id' => session('restaurant.id'),
                'table_id' => session('table.id'),
                'email' => $hiddenEmail,
                'type' => $hiddenEmail ? 'email' : 'download',
            ]
        );

        if ($pdf) {
            return $pdf->download('receipt.pdf');
        }


        return back()->with(['receipt-email-sent' => true]);
    }

    public function summary(Request $request)
    {
        $orders = $this->orderService->getCurrentOrders();

        return view('components.customer.order-summary', ['orders' => $orders]);
    }

    public function tryNewPayment(Order $order) {
        if($order->status !== Order::FAILED) {
            if($order->status == Order::INITIAL) {
                return redirect()->route('customer.cart');
            }
            return redirect()->route('customer.order.list');
        }

        try {
            $redirectUrl = $this->orderService->pay($order);

        } catch (\Exception $e) {
            return back()->with(['error' => __('payment-error')]);
        }

        return redirect($redirectUrl);
    }

    public function payCash(Order $order) {
        if($order->status !== Order::FAILED) {
            if($order->status == Order::INITIAL) {
                return redirect()->route('customer.cart');
            }
            return redirect()->route('customer.order.list');
        }

        try {
            $this->orderService->payCash($order);
            $redirectUrl = $this->orderService->pay($order);

        } catch (\Exception $e) {
            return back()->with(['error' => __('payment-error')]);
        }

        return redirect($redirectUrl);
    }
}
