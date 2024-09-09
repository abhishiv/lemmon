<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\OrderService;
use App\Models\Order;
use App\Payment\Models\Payment;
use App\Payment\PaymentService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
    }

    /**
     * After successful payment redirect remove
     * @throws \Exception
     */
    public function success(Payment $payment): RedirectResponse
    {
        //it's possible that the CheckPaymentConfirmation cron job might set the payment to confirmed so
        // there is no need to confirm the payment, we just redirect to the order page
        if ($payment->status == Payment::CONFIRMED) {
            $this->orderService->setSessionOrders($payment->payrexx->first()->reference_id);
            return redirect()->route('customer.order.list');
        }

        $orderID = $payment->payrexx->first()->reference_id;

        Log::channel('payments')->debug("Payrexx successfully redirected back with the following Payment: " . print_r($payment->id,
                true));

        $paymentService = new PaymentService($payment->provider, $payment->id);

        //Confirm redirect
        $paymentService->confirmRedirect($payment->payrexx->last()->toArray());

        //Confirm payment
        $confirmed = $paymentService->confirmPayment();

        if (!$confirmed) {
            $order = Order::find($payment->order_id);
            if ($order) {
                $order->update(['status' => Order::FAILED]);
            }
            return redirect(route('customer.payment.failed', $payment->id));
        }

        $this->orderService->setSessionOrders($orderID);

        return redirect()->route('customer.order.list');
    }

    public function failed(Payment $payment): Factory|View|Application|RedirectResponse
    {
        if ($payment->status == Payment::CONFIRMED) {
            return redirect()->route('customer.order.list');
        }

        $order = $payment->order;

        $order->update(['status' => Order::FAILED]);

        return view('customer.orders.failed', [
            'order' => $order
        ]);
    }

    public function cancel(Payment $payment)
    {
        if ($payment->status == Payment::CONFIRMED) {
            return redirect()->route('customer.order.list');
        }

        $order = $payment->order;

        $order->update(['status' => Order::FAILED]);

        return view('customer.orders.cancel', [
            'order' => $order
        ]);
    }
}
