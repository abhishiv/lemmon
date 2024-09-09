<?php

namespace App\Observers;

use App\Http\Services\OrderService;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Product;
use Carbon\Carbon;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     *
     * @param Order $order
     * @return void
     */
    public function created(Order $order): void
    {
        $order->events()->save(new OrderEvent([
            'event' => 'created',
            'causer_id' => auth()->id() ?? null,
            'status' => $order->status,
            'created_at' => Carbon::now(),
        ]));
    }

    /**
     * Handle the Order "updated" event.
     *
     * @param Order $order
     * @return void
     */
    public function updated(Order $order): void
    {
        $oldValues = $order->getOriginal();
        if (is_null($order->display_id) && is_null($order->parent_id)) {
            if ($order->status == Order::NEW || $order->status == Order::GROUP || ($order->status == Order::PREPARING && $order->restaurant->hasPrinterForType([Product::RESTAURANT]))) {
                $order->generateDisplayId();
            }
        }

        if ($order->status == Order::NEW || $order->status == Order::GROUP || ($order->status == Order::PREPARING && $order->restaurant->hasPrinterForType([Product::RESTAURANT]))) {
            $order->reloadStatuses();
        }

        if (is_null($order->parent_id)) {

            /*
             * This condition makes sure that notifications are sent only when:
             * - an order's status is not the same status as the one before;
             * - the order's status hasn't changed from new to preparing (because the notification would've already been sent when the order's status changed to new);
             * - the order's status hasn't moved backwards (e.g., from ready to preparing)
            */
            if (($order->status == Order::NEW || $order->status == Order::PREPARING) && !in_array($oldValues['status'],
                    [Order::NEW, Order::PREPARING, Order::READY, Order::CLOSED])) {
                $order->restaurant->sentNewOrderNotification();

                if (in_array($order->service_method, [Order::TAKEAWAY, Order::DELIVERY]) && $order->table) {
                    $orderService = new OrderService();

                    $orderService->sendOffsiteOrderNotification($order);
                }
            }
        }

        if ($order->table_id) {
            if (in_array($order->status, [Order::NEW, Order::PREPARING, Order::READY, Order::GROUP])) {
                $order->table->update([
                    'is_busy' => 1,
                ]);
            } else {
                if (in_array($order->status,
                        [Order::FAILED, Order::CLOSED, Order::CANCELED]) && !$order->table->activeOrders()->count()) {
                    $order->table->update([
                        'is_busy' => 0,
                    ]);
                }
            }
        }
        if ($order->status != $oldValues['status']) {
            $order->events()->save(new OrderEvent([
                'event' => 'updated',
                'causer_id' => auth()->id(),
                'status' => $order->status,
                'created_at' => Carbon::now(),
            ]));
        }
    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param Order $order
     * @return void
     */
    public function deleted(Order $order): void
    {
        $order->events()->save(new OrderEvent([
            'event' => 'deleted',
            'causer_id' => auth()->id(),
            'status' => $order->status,
            'created_at' => Carbon::now(),
        ]));
    }
}
