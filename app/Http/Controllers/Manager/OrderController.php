<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function list(): Factory|View|Application
    {
        return view('manager.orders.list');
    }

    /**
     * Display the specified resource.
     *
     * @param Order $order
     * @return Application|Factory|View
     */
    public function show(Order $order): Factory|View|Application
    {
        return view('manager.orders.show', compact('order'));
    }

    /**
     * @throws Exception
     */
    public function dataTable(): JsonResponse
    {
        $orders = Order::select([
            'id',
            'table_id',
            'amount',
            'tips',
            'status',
            'created_at',
            'display_id',
            'parent_id',
            'payment_method'
        ])
            ->whereNull('is_grouped')->orWhereNotNull('parent_id')
            ->with('payments', 'table', 'parent')->orderBy('id', 'desc')->get();

        return DataTables::of($orders)
            ->addColumn('payment', function ($order) {

                if ($order->partialPayments->isNotEmpty()) {
                    foreach ($order->partialPayments as $payment) {
                        if ($payment->method != $order->payment_method && $order->payment_method) {
                            return __('labels.payment-mixed');
                        }
                    }
                }

                if ($order->status == Order::CANCELED) {
                    return __('labels.payment-cancelled');
                }

                return $order->payments->isNotEmpty() ? $order->payments->last()->transaction_code : __('labels.payment-' . ($order->payment_method ?? 'unknown'));
            })
            ->editColumn('table_id', function ($order) {
                return $order->table->name ?? trans("labels.takeaway");
            })
            ->editColumn('created_at', function ($order) {
                return Carbon::make($order->created_at)->format('d/m/y H:i');
            })
            ->editColumn('status', function ($order) {
                return "<span class='status-{$order->status}'>" . trans('labels.' . $order->status) . "</span>";
            })
            ->editColumn('display_id', function ($order) {
                return !is_null($order->getDisplayId()) ? number_format($order->getDisplayId()) : '';
            })
            ->addColumn('id', function ($order) {
                return number_format($order->id);
            })
            ->editColumn('tips', function ($order) {
                return $order->tips ? 'CHF ' . priceFormat($order->tips) : '';
            })
            ->editColumn('amount', function ($order) {
                return 'CHF ' . priceFormat($order->amount);
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
