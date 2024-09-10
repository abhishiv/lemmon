<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffGetTipListRequest;
use App\Http\Services\OrderService;
use App\Http\Services\RestaurantTableService;
use App\Http\Services\StaffCartService;
use App\Models\AppliedDiscount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PartialPayment;
use App\Models\Product;
use App\Models\Extra;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\View\Components\Staff\TableActions;
use App\View\Components\Staff\TableStatus;
use App\View\Components\Staff\TableSummary;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected RestaurantTableService $restaurantTableService
    ) {
    }

    public function getPrinters(): JsonResponse
    {
        return response()->json(restaurant_settings_get('printers'));
    }

    public function tables(): Factory|View|Application
    {
        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        $tables = RestaurantTable::where('restaurant_id', $restaurant->id)
            ->where('type', RestaurantTable::SERVE)
            ->where('status', RestaurantTable::AVAILABLE)->get();

        $rooms = $tables->pluck('room')->filter()->unique();

        $availablePrinters = $restaurant->getAvailablePrinterTypes();

        return view('staff.tables.index', compact('restaurant', 'availablePrinters', 'rooms', 'tables'));
    }

    public function menu(Request $request): Factory|View|Application
    {
        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        $cartService = new StaffCartService($request);

        $cartOptions = $cartService->getCartOptions();

        $table = $cartService->getTable();

        $services = Service::with([
            'products' => function ($query) {
                $query->with([
                    'bundles',
                    'bundles.extras' => function ($extrasQuery) {
                        $extrasQuery->where('status', Extra::AVAILABLE);
                    },
                    'bundles.extraProducts' => function ($extraProductsQuery) {
                        $extraProductsQuery->where('status', Product::AVAILABLE);
                    },
                    'services'
                ]);
            }
        ])
            ->where('restaurant_id', $restaurant->id)
            ->where('status', Service::ACTIVE)
            ->whereHas('serviceTypes', function ($query) {
                $query->where('alias', Service::SERVE);
            })
            ->orderBy('order')
            ->get();

        $services = $services->filter(function ($service) {
            return $service->isAvailable(true);
        });

        foreach ($services as $service) {
            $service->categories = $service->productsByCategories(true);
        }

        $availablePrinters = $restaurant->getAvailablePrinterTypes();

        $menuQuantities = $cartService->getQuantitiesByProductId();

        $tables = RestaurantTable::select('id', 'name')
            ->where('restaurant_id', $restaurant->id)
            ->where('type', RestaurantTable::SERVE)
            ->where('status', RestaurantTable::AVAILABLE)
            ->orderBy('name')
            ->get();

        return view('staff.menu.index',
            compact('restaurant', 'table', 'services', 'availablePrinters', 'cartOptions', 'menuQuantities', 'tables'));
    }

    public function index(Request $request): Factory|View|Application
    {
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

        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        $availablePrinters = $restaurant->getAvailablePrinterTypes();

        $tables = RestaurantTable::where('restaurant_id', $restaurant->id)
            ->where('type', RestaurantTable::SERVE)
            ->where('status', RestaurantTable::AVAILABLE)
            ->orderBy('name')
            ->get();

        $selectedTable = $request->table_id ?? null;

        return view('staff.orders.index',
            compact('orders', 'restaurant', 'availablePrinters', 'tables', 'selectedTable'));
    }

    public function products(): View|string
    {
        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        $availablePrinters = $restaurant->getAvailablePrinterTypes();

        $products = Product::select('id', 'name', 'status')->where('restaurant_id', $restaurant->id)->without([
            'images',
            'services'
        ])->get();

        $extras = Extra::select('id', 'title', 'status')->where('restaurant_id', $restaurant->id)->get();

        return view('staff.products.index', compact('availablePrinters', 'products', 'extras'));
    }

    public function updateProduct(Request $request)
    {
        if (!$request->id) {
            return response()->json(['success' => false]);
        }

        if ($request->type === 'extra') {
            $extra = Extra::find($request->id);
            $extra->status = $request->status === 'enable' ? Extra::AVAILABLE : Extra::UNAVAILABLE;
            $extra->save();
        } else {
            if ($request->type === 'product') {
                $product = Product::find($request->id);
                $product->status = $request->status === 'enable' ? Product::AVAILABLE : Product::OUTOFSTOCK;
                $product->save();
            }
        }

        return response()->json(['success' => true]);
    }

    // public function completed(Restaurant $restaurant): Factory|View|Application
    // {
    //     $orders = $this->orderService->closedOrders();

    //     return view('staff.dashboard.completed', compact('orders', 'restaurant'));
    // }

    public function getTableList(Request $request): JsonResponse
    {
        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        $tables = RestaurantTable::select('id', 'name', 'room', 'is_busy')
            ->where('restaurant_id', $restaurant->id)
            ->where('type', RestaurantTable::SERVE)
            ->where('status', RestaurantTable::AVAILABLE)
            ->with([
                'orders' => function ($query) {
                    $query->whereNotIn('status', [Order::CLOSED, Order::CANCELED, Order::INITIAL, Order::FAILED]);
                }
            ]);

        $room = $request->room ?? '';
        $status = $request->status ?? '';

        $dataTable = DataTables::eloquent($tables);

        $dataTable->filter(function ($query) use ($room, $status) {
            if ($room !== '') {
                $query->where('room', $room);
            }

            if ($status === 'busy') {
                $query->where('is_busy', 1);
            }
        });

        $dataTable->editColumn('room', function ($table) {
            return $table->room ?? '-';
        });

        $dataTable->editColumn('is_busy', function ($table) {

            return Blade::renderComponent(new TableStatus($table->id, $table->is_busy, $table->orders->count()));
        });

        $dataTable->addColumn('actions', function ($table) {
            return Blade::renderComponent(new TableActions($table->id, $table->orders->count(), $table->name));
        });

        $dataTable->addColumn('active', function ($table) {
            return $table->activeOrders()->count();
        });

        $dataTable->removeColumn('orders');

        $dataTable->rawColumns(['is_busy', 'actions']);

        return $dataTable->toJson();
    }

    public function updateTableStatus(Request $request): JsonResponse
    {
        if (!$request->table_id || !$request->new_status) {
            return response()->json(['success' => false]);
        }

        $table = RestaurantTable::find($request->table_id);

        $table->update([
            'is_busy' => $request->new_status === 'busy' ? 1 : 0,
        ]);

        return response()->json(['success' => true]);
    }

    public function tableSummary(Request $request): JsonResponse
    {
        if (!$request->id) {
            return response()->json(['success' => false, 'message' => 'Missing table id']);
        }

        $orders = $this->getTableActiveOrders($request->id);

        if (!$orders->count()) {
            return response()->json(['success' => false, 'message' => 'Missing orders']);
        }

        $mergedOrders = $this->orderService->mergeOrders($orders);

        return response()->json([
            'success' => true,
            'html' => Blade::renderComponent(new TableSummary($mergedOrders, $request->id)),
            'summary' => $mergedOrders,
        ]);
    }

    private function getTableActiveOrders($tableId)
    {
        return Order::where('table_id', $tableId)
            ->whereNotIn('status', [Order::CLOSED, Order::CANCELED, Order::INITIAL, Order::FAILED])
            ->where('service_method', Order::DINEIN)
            ->whereNotNull('parent_id')
            ->get();
    }

    public function viewTipList(): Factory|View|Application
    {
        $tipList = $this->orderService->tipList();

        return view('staff.tips.index', $tipList);
    }

    public function getTipList(StaffGetTipListRequest $request): JsonResponse
    {
        return new JsonResponse($this->orderService->tipList($request));
    }

    public function onesignal(Request $request): JsonResponse
    {
        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        $onesignalDeviceIds = json_decode($restaurant->onesignal_device_ids);

        if (!is_array($onesignalDeviceIds)) {
            $onesignalDeviceIds = [$request->deviceId];
        } elseif (!in_array($request->deviceId, $onesignalDeviceIds)) {
            $onesignalDeviceIds[] = $request->deviceId;
        }

        $restaurant->onesignal_device_ids = json_encode($onesignalDeviceIds);

        $restaurant->save();

        return response()->json(['success' => true]);
    }

    public function setPaymentOptions(Request $request)
    {
        if (isset($request->clear_all)) {
            session()->forget('payment_options');

            session()->save();

            return response()->json([
                'success' => true,
                'options' => session()->get('payment_options'),
            ]);
        }

        if (!$request->order_id) {
            return response()->json(['success' => false]);
        }

        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json(['success' => false]);
        }

        session()->put('payment_options.order_id', $request->order_id);

        if (isset($request->tips)) {
            if ($request->tips == 0) {
                session()->forget('payment_options.tips');
            } else {
                session()->put('payment_options.tips', $request->tips);
            }

            session()->save();

            return response()->json([
                'success' => true,
                'totals' => $this->getTotalsForOrder($order),
            ]);
        }

        if (isset($request->discount)) {
            // Takeaway discount and manual discount don't stack,
            if (is_null($order->discount_takeaway)) {
                if ($request->discount['amount'] == 0) {
                    session()->forget('payment_options.discount');
                } else {
                    session()->put('payment_options.discount.type', $request->discount['type']);
                    session()->put('payment_options.discount.amount', $request->discount['amount']);
                }

                session()->save();
            }

            return response()->json([
                'success' => true,
                'totals' => $this->getTotalsForOrder($order),
            ]);
        }
    }

    public function getTotalsForPayment(Request $request): JsonResponse
    {
        if (!$request->order_id) {
            return response()->json(['success' => false]);
        }

        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json(['success' => false]);
        }

        return response()->json([
            'success' => true,
            'order_id' => $request->order_id,
            'totals' => $this->getTotalsForOrder($order),
        ]);
    }

    private function getTotalsForOrder($order): array
    {
        $totals = [
            'currency' => __('labels.currency'),
            'subtotal' => 0,
            'discount_takeaway' => $order->discount_takeaway,
        ];

        if ($order->isParent) {
            $children = $order->children()
                ->where(function ($query) {
                    $query->where('payment_method', Order::CASH)
                        ->orWhereNull('payment_method');
                })
                ->get();

            foreach ($children as $childOrder) {
                $totals['subtotal'] += $this->orderService->calculateSubtotalAmount($childOrder);
            }
        } else {
            $totals['subtotal'] += $this->orderService->calculateSubtotalAmount($order);
        }

        $paymentOptions = $this->getPaymentOptions($order);

        $totals['subtotal'] = priceFormat($totals['subtotal']);
        $totals['gross'] = $totals['subtotal'];

        // Takeaway discount and manual discount don't stack
        if (!is_null($totals['discount_takeaway'])) {
            // The takeaway discount is included when the order is placed from the Client App
            $totals['gross'] = priceFormat($order->amount);
        } else {
            if (isset($paymentOptions['discount'])) {
                $totals['manual_discount'] = $this->calculateManualDiscount($paymentOptions['discount'],
                    $totals['subtotal']);
                $totals['gross'] = priceFormat($totals['subtotal'] - $totals['manual_discount']['net_amount']);
            }
        }

        $totals['amount_due'] = $totals['gross'];

        if (isset($paymentOptions['tips'])) {
            $totals['tips']['value'] = priceFormat($paymentOptions['tips']);
            $totals['tips']['suffix'] = strtolower(__('labels.tips'));
            $totals['amount_due'] = priceFormat($totals['amount_due'] + $totals['tips']['value']);
        }

        return $totals;
    }

    private function getPaymentOptions(Order $order)
    {
        $paymentOptions = session()->get('payment_options');

        if (!isset($paymentOptions['order_id']) || $paymentOptions['order_id'] != $order->id) {
            $paymentOptions = null;
        }

        return $paymentOptions;
    }

    private function calculateManualDiscount($discount, $subtotal)
    {
        $discount['suffix'] = strtolower(__('labels.discount'));

        if ($discount['type'] === 'percentage') {
            $discount['net_amount'] = priceFormat(($subtotal * $discount['amount']) / 100);
        } else {
            $discount['net_amount'] = priceFormat($discount['amount']);
        }

        return $discount;
    }

    public function updatePayment(Request $request): JsonResponse
    {
        if (!$request->order_id || !$request->payment_method) {
            return response()->json(['success' => false]);
        }

        $order = Order::find($request->order_id);

        $children = false;

        if ($order->isParent) {
            if ($order->user_id) {
                $children = $order->children;
            } else {
                $children = $order->children()
                    ->where(function ($query) {
                        $query->where('payment_method', Order::CASH)
                            ->orWhereNull('payment_method');
                    })
                    ->get();
            }
        }

        $this->updatePaymentMethod($request->payment_method, $order, $children);

        $paymentOptions = $this->getPaymentOptions($order);

        if ($request->tips) {
            $this->updateTips($request->tips, $order, $children);
        }

        $this->updatePartialTips($order, $children);

        if ($paymentOptions) {
            if (isset($paymentOptions['discount'])) {
                if ($paymentOptions['discount']['type'] === 'percentage') {
                    $this->updatePercentageDiscount($paymentOptions['discount']['amount'], $order, $children);
                } else {
                    if ($paymentOptions['discount']['type'] === 'fixed-amount') {
                        $this->updateFixedAmountDiscount($paymentOptions['discount']['amount'], $order, $children);
                    }
                }
            }
        }

        session()->forget('payment_options');
        session()->save();


        $this->orderService->updateAllOrderStatuses($order, Order::CLOSED);

        return response()->json([
            'success' => true,
        ]);
    }

    public function removeOrderItem(Request $request): JsonResponse
    {
        if (empty($request->items)) {
            return response()->json(["success" => false, "message" => "Please select the items you want to remove"]);
        }

        return $this->orderService->removeItem($request['items']);
    }

    public function changeTable(Request $request): JsonResponse
    {
        if (!$request->old_table_id || !$request->new_table_id) {
            return response()->json(['success' => false, 'message' => 'Missing table id']);
        }

        //if the new table already has orders, we need to return a message first
        $orders = Order::where('table_id', $request->new_table_id)
            ->whereNotIn('status', [Order::CLOSED, Order::CANCELED, Order::INITIAL, Order::FAILED])
            ->where('service_method', Order::DINEIN)
            ->whereNotNull('parent_id')
            ->get();

        if ($orders->count() && !$request->force) {
            return response()->json(['success' => false, 'message' => 'The table already has orders, do you continue?', 'force' => true]);
        }

        $this->restaurantTableService->checkOrderTable($request->new_table_id, $request->old_table_id);

        return response()->json(['success' => true, 'message' => 'The table has been changed']);
    }

    private function updatePaymentMethod($method, $order, $children): void
    {
        $order->update(['payment_method' => $method]);

        if ($children) {
            foreach ($children as $childOrder) {
                $childOrder->update(['payment_method' => $method]);
            }
        }
    }

    private function updateTips($tips, $order, $children): void
    {
        if ($children) {
            $baseAmount = floor($tips / $children->count());

            foreach ($children as $childOrder) {
                $tipsForCurrentOrder = ($tips - $baseAmount) >= 0 ? $baseAmount : 0;

                $childOrder->update(['tips' => $tipsForCurrentOrder]);
                $tips -= $tipsForCurrentOrder;
            }

            if ($tips > 0) {
                $firstChildOrder = $children->first();
                $firstChildOrder->tips += $tips;
                $firstChildOrder->save();
            }
        } else {
            $order->update(['tips' => $tips]);
        }

    }

    private function updatePartialTips($orders, $children = []): void
    {
        if ($children) {
            foreach ($children as $childOrder) {
                $childOrder->update(['tips' => $childOrder->tips + $childOrder->partial_tips]);
            }
        } else {
            if ($orders instanceof Collection) {
                foreach ($orders as $order) {
                    $order->update(['tips' => $order->tips + $order->partial_tips]);
                }
            } else {
                $orders->update(['tips' => $orders->tips + $orders->partial_tips]);
            }
        }
    }

    private function updatePercentageDiscount($discount, $order, $children): void
    {
        if ($children) {
            $discountBreakdown = [];

            foreach ($children as $childOrder) {

                $discountBreakdown[] = [
                    'order' => $childOrder,
                    'source' => 'staff',
                    'type' => 'percentage',
                    'amount' => $discount,
                    'target_sum' => $childOrder->amount,
                    'net' => $childOrder->amount * $discount / 100,
                ];
            }

            $this->storeDiscounts($discountBreakdown);
        } else {
            $net = $order->amount * $discount / 100;

            DB::transaction(function () use ($order, $discount, $net) {
                AppliedDiscount::create([
                    'order_id' => $order->id,
                    'source' => 'staff',
                    'type' => 'percentage',
                    'amount' => $discount,
                    'target_sum' => $order->amount,
                    'net' => $net,
                ]);

                $order->update(['amount' => ($order->amount - $net)]);
            });
        }
    }

    private function updateFixedAmountDiscount($discount, $order, $children)
    {
        if ($children) {
            $baseAmount = floor($discount / $children->count());

            $discountBreakdown = [];

            $remainingAmount = $discount;

            foreach ($children as $childOrder) {
                $discountForCurrentOrder = ($remainingAmount - $baseAmount) >= 0 ? $baseAmount : 0;

                $discountBreakdown[] = [
                    'order' => $childOrder,
                    'source' => 'staff',
                    'type' => 'fixed-amount',
                    'amount' => $discount,
                    'target_sum' => $childOrder->amount,
                    'net' => $discountForCurrentOrder,
                ];

                $remainingAmount -= $discountForCurrentOrder;
            }

            if ($remainingAmount > 0) {
                $discountBreakdown[0]['net'] += $remainingAmount;
            }

            $this->storeDiscounts($discountBreakdown);
        } else {
            DB::transaction(function () use ($order, $discount) {
                AppliedDiscount::create([
                    'order_id' => $order->id,
                    'source' => 'staff',
                    'type' => 'fixed-amount',
                    'amount' => $discount,
                    'target_sum' => $order->amount,
                    'net' => $discount,
                ]);

                $order->update(['amount' => ($order->amount - $discount)]);
            });
        }

        return true;
    }

    private function storeDiscounts($discounts)
    {
        DB::transaction(function () use ($discounts) {

            foreach ($discounts as $discount) {
                $order = $discount['order'];
                AppliedDiscount::create([
                    'order_id' => $order->id,
                    'source' => $discount['source'],
                    'type' => $discount['type'],
                    'amount' => $discount['amount'],
                    'target_sum' => $discount['target_sum'],
                    'net' => $discount['net'],
                ]);

                $order->update(['amount' => ($order->amount - $discount['net'])]);
            }
        });
    }

    public function getTotalsForTable(Request $request): JsonResponse
    {
        if (!$request->tableId) {
            return response()->json([
                'success' => false,
            ]);
        }

        $orders = $this->getTableActiveOrders($request->tableId);

        if (!$orders->count()) {
            return response()->json([
                'success' => false,
            ]);
        }

        if (!empty($request['partial'])) {
            return response()->json([
                'success' => true,
                'result' => $this->orderService->mergePartialOrders($orders, $request['partial']),
                'partial' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'result' => $this->orderService->mergeOrders($orders),
            'partial' => false,
        ]);
    }

    public function setPaymentOptionsForTable(Request $request)
    {
        if (isset($request->clear_all)) {
            session()->forget('table_payment_options');

            session()->save();

            return response()->json([
                'success' => true,
                'options' => session()->get('table_payment_options'),
            ]);
        }

        if (!$request->table_id && !count($request->orders)) {
            return response()->json(['success' => false]);
        }

        $table = RestaurantTable::find($request->table_id);

        if (!$table) {
            return response()->json(['success' => false]);
        }

        session()->put('table_payment_options.table_id', $request->table_id);

        $orders = $table->orders()->whereIn('id', $request->orders)->get();

        if (isset($request->tips)) {
            if ($request->tips == 0) {
                session()->forget('table_payment_options.tips');
            } else {
                session()->put('table_payment_options.tips', $request->tips);
            }

            session()->save();

            return response()->json([
                'success' => true,
                'result' => $this->orderService->mergeOrders($orders, true),
            ]);
        }

        if (isset($request->discount)) {
            if ($request->discount['amount'] == 0) {
                session()->forget('table_payment_options.discount');
            } else {
                session()->put('table_payment_options.discount.type', $request->discount['type']);
                session()->put('table_payment_options.discount.amount', $request->discount['amount']);
            }

            session()->save();

            if (!empty($request->partial)) {
                return response()->json([
                    'success' => true,
                    'result' => $this->orderService->mergePartialOrders($orders, $request->partial, $request),
                    'partial' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'result' => $this->orderService->mergeOrders($orders, true),
            ]);
        }
    }

    public function partialPay($orders, $request)
    {

        DB::beginTransaction();

        try {

            // Flatten the orders into items with priorities
            $orderItems = [];
            $amount = 0;

            //Add a partial tip just for the first order
            $updateOrderTip = Order::find($orders[0]->id);
            $updateOrderTip->partial_tips = $updateOrderTip->partial_tips + $request['tips'];
            $updateOrderTip->save();

            foreach ($orders as $order) {

                foreach ($order->items as $orderItem) {
                    $orderItems[] = [
                        'order' => $order,
                        'item' => $orderItem,
                        'bundle' => $orderItem->itemBundles,
                        'priority' => count($order->items),
                        'table_id' => $order->table_id,
                        'restaurant_id' => $order->restaurant_id,
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'price' => $orderItem->products->price,
                    ];
                }
            }

            // Sort order items by priority
            usort($orderItems, function ($a, $b) {
                return $a['priority'] <=> $b['priority'];
            });

            // Process partial payments
            foreach ($request->partial as $key => $partial) {

                $parts = explode('_', $key);
                $productId = $parts[0];
                $type = $parts[1] ?? null;
                $extraId = $parts[2] ?? null;
                $price = $parts[3] ?? null;
                //dd($request->partial);

                $itemQtyToPay = 0;
                foreach ($orderItems as $index => $orderItemData) {

                    $orderItem = $orderItemData['item'];
                    $bundle = $orderItemData['bundle'];

                    if ($orderItem->product_id == $productId && $orderItem->status !== OrderItem::PAID) {

                        $orderIds[$orderItemData['order']['id']] = $orderItemData['order']['id'];

                        if ($extraId && $type) {
                            // Check bundles
                            foreach ($bundle as $bundleItem) {
                                if ($bundleItem->entity_id == $extraId && $bundleItem->entity_type == ($type === 'extras' ? Extra::class : Product::class)) {
                                    // If the bundle matches, update the order item
                                    $itemQtyToPay = min($partial['qty'],
                                        $orderItem->quantity - $orderItem->paid_quantity);
                                    $orderItem->paid_quantity += $itemQtyToPay;
                                    if ($orderItem->paid_quantity >= $orderItem->quantity) {
                                        $orderItem->status = OrderItem::PAID;
                                    }
                                    $orderItem->save();

                                    $amount += $bundleItem['price'] * $itemQtyToPay;

                                    $partial['qty'] -= $itemQtyToPay;
                                    if ($partial['qty'] <= 0) {
                                        break;
                                    }
                                }
                            }
                        } else {
                            // Check main order items
                            $itemQtyToPay = min($partial['qty'], $orderItem->quantity - $orderItem->paid_quantity);
                            $orderItem->paid_quantity += $itemQtyToPay;
                            if ($orderItem->paid_quantity >= $orderItem->quantity) {
                                $orderItem->status = OrderItem::PAID;
                            }
                            $orderItem->save();

                            $partial['qty'] -= $itemQtyToPay;
                        }

                        $amount += $orderItemData['price'] * $itemQtyToPay;

                        $partialPayments[] = [
                            'restaurant_id' => $orderItemData['restaurant_id'],
                            'table_id' => $orderItemData['table_id'],
                            'order_id' => $orderItemData['order_id'],
                            'order_item_id' => $orderItemData['order_item_id'],
                            'product_id' => $orderItemData['product_id'],
                            'quantity' => $itemQtyToPay,
                            'price' => $orderItemData['price'],
                        ];
                    }

                    if ($partial['qty'] <= 0) {
                        break;
                    }
                }
            }


            if (session()->has('table_payment_options')) {
                $request['discount'] = session()->get('table_payment_options')['discount'];
                $amount -= $this->calculateManualDiscount($request['discount'], $amount)['net_amount'];
            }

            $partialId = PartialPayment::create([
                'restaurant_id' => auth()->user()->restaurant_id,
                'table_id' => $request['table_id'],
                'user_id' => auth()->user()->id,
                'orders' => implode(',', $orderIds),
                'cart' => json_encode($partialPayments, 1),
                'method' => $request['payment_method'],
                'discount' => $request['discount']['amount'] ?? null,
                'discount_type' => $request['discount']['type'] ?? null,
                'tips' => $request['tips'],
                'selection' => json_encode($request['partial'], 1),
                'amount' => $amount,
            ]);

            $partialId->orders()->attach($orderIds, ['created_at' => now(), 'updated_at' => now()]);

            DB::commit();
        } catch (\Exception $e) {
            Log::channel('payments')->warning("Partial payment failed: " . print_r($e, true));
            DB::rollback();
            return response()->json(['error' => 'Failed to update order items.'], 500);
        }

        return $partialId;

    }

    public function updatePaymentForTable(Request $request): JsonResponse
    {

        if (!$request->table_id || !count($request->close) || !count($request->payment) || !$request->payment_method) {
            return response()->json(['success' => false]);
        }

        $table = RestaurantTable::find($request->table_id);

        if (!$table) {
            return response()->json(['success' => false]);
        }

        $orders = $table->orders()->with('items')->whereIn('id', $request->payment)->get();

        if (!empty($request->partial)) {

            $partialPayment = $this->partialPay($orders, $request);

            return response()->json([
                'success' => true,
                'partial' => $request->partial,
                'payment_id' => $partialPayment->id
            ]);
        }

        foreach ($orders as $order) {
            $order->update(['payment_method' => $request->payment_method]);
            $order->parent->update(['payment_method' => $request->payment_method]);
        }

        if ($request->tips) {
            $this->updateTipsForTable($request->tips, $orders);
        }

        $paymentOptions = $this->getPaymentOptionsForTable($table);

        if ($paymentOptions) {
            if (isset($paymentOptions['discount'])) {
                if ($paymentOptions['discount']['type'] === 'percentage') {
                    $this->updatePercentageDiscountForTable($paymentOptions['discount']['amount'], $orders);
                } else {
                    if ($paymentOptions['discount']['type'] === 'fixed-amount') {
                        $this->updateFixedAmountDiscountForTable($paymentOptions['discount']['amount'], $orders);
                    }
                }
            }
        }

        session()->forget('table_payment_options');
        session()->save();

        $this->updatePartialTips($orders);

        $ordersToClose = $table->orders()->whereIn('id', $request->close)->get();

        // All orders at the table have to be closed,
        // including the ones with ONLINE payment, which are considered paid
        $this->orderService->closeOrdersAndParents($ordersToClose);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getPaymentOptionsForTable(RestaurantTable $table)
    {
        $paymentOptions = session()->get('table_payment_options');

        if (!isset($paymentOptions['table_id']) || $paymentOptions['table_id'] != $table->id) {
            $paymentOptions = null;
        }

        return $paymentOptions;
    }

    private function updateTipsForTable($tips, $orders): bool
    {
        if ($orders->count() > 1) {
            $baseAmount = floor($tips / $orders->count());

            foreach ($orders as $order) {
                $tipsForCurrentOrder = ($tips - $baseAmount) >= 0 ? $baseAmount : 0;

                $order->update(['tips' => $tipsForCurrentOrder]);
                $tips -= $tipsForCurrentOrder;
            }

            if ($tips > 0) {
                $firstOrder = $orders->first();
                $firstOrder->tips += $tips;
                $firstOrder->save();
            }
        } else {
            $orders->first()->update(['tips' => $tips]);
        }

        return true;
    }

    private function updatePercentageDiscountForTable($discount, $orders)
    {
        foreach ($orders as $order) {
            $discountBreakdown[] = [
                'order' => $order,
                'source' => 'staff',
                'type' => 'percentage',
                'amount' => $discount,
                'target_sum' => $order->amount,
                'net' => $order->amount * $discount / 100,
            ];
        }

        $this->storeDiscounts($discountBreakdown);

        return true;
    }

    private function updateFixedAmountDiscountForTable($discount, $orders)
    {
        $discountBreakdown = [];

        if ($orders->count() > 1) {
            $baseAmount = floor($discount / $orders->count());

            $remainingAmount = $discount;

            foreach ($orders as $order) {
                $discountForCurrentOrder = ($remainingAmount - $baseAmount) >= 0 ? $baseAmount : 0;

                $discountBreakdown[] = [
                    'order' => $order,
                    'source' => 'staff',
                    'type' => 'fixed-amount',
                    'amount' => $discount,
                    'target_sum' => $order->amount,
                    'net' => $discountForCurrentOrder,
                ];

                $remainingAmount -= $discountForCurrentOrder;
            }

            if ($remainingAmount > 0) {
                $discountBreakdown[0]['net'] += $remainingAmount;
            }
        } else {
            $order = $orders->first();

            $discountBreakdown[] = [
                'order' => $order,
                'source' => 'staff',
                'type' => 'fixed-amount',
                'amount' => $discount,
                'target_sum' => $order->amount,
                'net' => $discount,
            ];
        }

        $this->storeDiscounts($discountBreakdown);

        return true;
    }

    /*private function closeOrdersAndParents($orders): void
    {
        foreach ($orders as $order) {
            $order->update([
                'status' => Order::CLOSED,
                'restaurant_status' => Order::CLOSED,
                'bar_status' => Order::CLOSED,
            ]);

            $order->foodStatuses()->update([
                'status' => Order::CLOSED,
            ]);

            if ($order->parent) {
                $order->parent->update([
                    'status' => Order::CLOSED,
                    'restaurant_status' => Order::CLOSED,
                    'bar_status' => Order::CLOSED
                ]);
            }
        }
    }*/

    public function closeOrdersForTable(Request $request)
    {
        if (!$request->table_id || !$request->orders) {
            return response()->json(['success' => false]);
        }

        $orders = Order::whereIn('id', $request->orders)
            ->where('table_id', $request->table_id)
            ->get();

        if (!$orders->count()) {
            return response()->json(['success' => false]);
        }

        $this->orderService->closeOrdersAndParents($orders);

        return response()->json(['success' => true]);
    }
}
