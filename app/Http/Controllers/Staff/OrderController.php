<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderFormRequest;
use App\Http\Services\OrderService;
use App\Http\Services\PrinterService;
use App\Models\Order;
use App\Models\Product;
use App\Models\Restaurant;
use App\View\Components\Staff\ListOrders;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function update(OrderFormRequest $request)
    {
        $this->orderService->update($request);

        return $this->get();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
public function get()
{
    // Retrieve active and closed orders with eager loading to prevent N+1 queries
    $orders = $this->orderService->activeOrders()
        ->merge($this->orderService->closedOrders())
        ->load([
            'foodStatuses',
            'items.products',
            'items.itemBundles.entity',
            'children' => function ($query) {
                $query->where('payment_method', Order::ONLINE);
            }
        ]);

    $restaurant = Restaurant::with('foodTypes')->find(auth()->user()->restaurant_id);

    $availablePrinters = $restaurant->getAvailablePrinterTypes();

    return Blade::renderComponent(new ListOrders($orders, $restaurant, $availablePrinters));
}

    public function ordersOverview(Request $request): array
    {
        $status = $request->status ?? 'active';

        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        return [
            'html' => Blade::render("<x-staff.orders-overview status='$status'/>"),
            'orders_to_print' => $this->getOrdersToPrint($restaurant),
            'print_receipt_url' => $restaurant->hasPrinterForType([Restaurant::RECEIPT]) ? route('staff.order.print.receipt') : '',
        ];
    }

    public function cancel(Request $request, Order $order)
    {
        $order->update([
            'status' => Order::CANCELED,
            'restaurant_status' => Order::CANCELED,
            'bar_status' => Order::CANCELED,
        ]);

        $order->children()->update([
            'status' => Order::CANCELED,
            'restaurant_status' => Order::CANCELED,
            'bar_status' => Order::CANCELED,
        ]);

        return $this->get();
    }

    public function receipt(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'order' => ['required', 'numeric', 'exists:orders,id']
        ]);

        $this->orderService->sendReceipt($request->email, $request->order);

        return response()->json(['success' => true]);
    }

    public function getOrdersToPrint()
    {
        $printerService = new PrinterService($this->orderService);

        return response()->json($printerService->getAllOrders());
    }

    public function printReceipt(Request $request): JsonResponse
    {
        $printerService = new PrinterService($this->orderService);

        if ($request->type == 'single' && $request->order_id) {
            $order = Order::find($request->order_id);

            return response()->json($printerService->getReceiptPrintObject($order, $request->onlyCash ?? false));
        }

        if ($request->type == 'merged' && $request->jobs && count($request->jobs)) {
            $jobs = [];

            foreach ($request->jobs as $job) {
                $orders = Order::whereIn('id', $job['orders'])->get();

                if ($orders->count()) {
                    if ($request['partial']) {
                        $jobs[] = $this->orderService->mergePartialOrders($orders, $request['partial'], $request);
                    } else {
                        $jobs[] = $this->orderService->mergeOrders($orders, false, true);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'jobs' => $printerService->getReceipts($jobs),
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function updatePrintStatus(Request $request, Order $order): JsonResponse
    {
        $productType = $request->productType ?? null;
        $status = $request->status ?? null;

        if (!$productType || !in_array($productType, Product::TYPES) || !$status || !in_array($status,
                Order::PRINTSTATUS)) {
            return response()->json(['success' => false]);
        }

        $order->{$productType . '_ticket_printed'} = $status;
        $order->save();

        return response()->json(['success' => true]);
    }
}
