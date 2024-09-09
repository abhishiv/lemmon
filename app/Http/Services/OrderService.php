<?php

namespace App\Http\Services;


use App\Models\PartialPayment;
use Exception;
use Carbon\Carbon;
use App\Models\Extra;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\OrderFoodStatus;
use App\Models\OrderItemBundle;
use App\Models\RestaurantTable;
use App\Payment\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Jobs\NewReceiptNotification;
use App\Http\Requests\OrderFormRequest;
use Illuminate\Support\Facades\Session;
use App\Jobs\NewOrderStatusNotification;
use App\Models\AppliedDiscount;
use Illuminate\Database\Eloquent\Collection;
use App\Payment\Processors\Payrexx\PayrexxPaymentService;
use App\Jobs\SendNewTakeawayOrderNotification;
use App\Jobs\SendNewDeliveryOrderNotification;
use App\Models\DeliveryInformation;
use App\Models\PickupDetails;

class OrderService
{
    public function orders(): array
    {
        $orders = [];

        if (!session()->has('orders')) {
            abort(404);
        }

        $sessionOrders = session('orders');

        if (empty($sessionOrders)) {
            return $orders;
        }

        session()->forget('orders');

        foreach ($sessionOrders as $orderId) {
            $orders[] = Order::where('id', $orderId)->whereIn('status', Order::STAFFSTATUS)->get()->first();
        }

        $orders = array_filter($orders, fn($value) => !is_null($value));

        if (empty($orders)) {
            session()->put('orders', []);
            return $orders;
        }

        foreach ($orders as $order) {
            session()->put('orders.' . $order->id, $order->id);
        }
        return $orders;
    }

    /**
     * Store the order && order items
     * @throws Exception
     */
    public function store($request)
    {
        $table = RestaurantTable::find(session('table.id'));
        if ($table->status == RestaurantTable::UNAVAILABLE) {
            return 'unavailable';
        }

        if (!$table->restaurant->valid_working_hours) {
            return 'unavailable';
        }

        $cartService = new CartService();

        $totals = $cartService->getTotals($request);

        return DB::transaction(function () use ($request, $table, $totals) {
            $order = Order::create([
                'table_id' => ($request->service_method !== Order::DINEIN && $table->type === RestaurantTable::SERVE) ? null : $table->id,
                'restaurant_id' => $table->restaurant_id,
                'take_away' => session('cart.takeaway') ?? null,
                'service_method' => $request->service_method,
                'discount_takeaway' => $totals['discount'] && ($totals['discount_type'] === 'takeaway') ? restaurant_settings_get('discount_takeaway',
                    $table->restaurant) : null,
                'amount' => $totals['total'],
                'tips' => ($request->input('tips') !== null && $request->input('tips') == (string)(float)$request->input('tips')) ? floatval($request->input('tips')) : null,
                'delivery_fee' => $request->service_method === Order::DELIVERY ? $table->restaurant->getDeliveryFeeForCity($request->city) : null,
                'notes' => $request->notes,
                'payment_method' => $request->payment_method,
                'session_id' => session()->getId(),
                'status' => Order::INITIAL,
                'restaurant_status' => Order::INITIAL,
                'bar_status' => Order::INITIAL,
                'device_id' => session('deviceId') ?: null,
            ]);

            foreach ($request->items as $key => $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item,
                    'quantity' => (!is_numeric($request->quantity[$key]) || $request->quantity[$key] < 1) ? 1 : $request->quantity[$key],
                    'notes' => $request->itemNotes[$key],
                    'price' => Product::find($item)->salePrice
                ]);
                if (isset($request->bundle[$key])) {
                    foreach ($request->bundle[$key] as $number => $bundles) {
                        foreach ($bundles as $type => $items) {
                            if ($type === 'Product') {
                                $type = Product::class;
                            } elseif ($type === 'Extra') {
                                $type = Extra::class;
                            }
                            foreach ($items as $index => $itemBundles) {
                                foreach ($itemBundles as $itemBundled) {
                                    OrderItemBundle::create([
                                        'order_item_id' => $orderItem->id,
                                        'entity_id' => $itemBundled['id'],
                                        'entity_type' => $type,
                                        'price' => $itemBundled['price'],
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            if ($request->service_method === Order::DELIVERY) {
                DeliveryInformation::create([
                    'order_id' => $order->id,
                    'first_name' => $request->first_name ?? null,
                    'last_name' => $request->last_name ?? null,
                    'company_name' => $request->company_name ?? null,
                    'street' => $request->street,
                    'postal_code' => $request->postal_code,
                    'city' => $request->city,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'notes' => $request->delivery_notes,
                ]);
            }

            if ($request->service_method === Order::TAKEAWAY && $request->pickup === 'later') {
                $carbonDate = Carbon::parse($request->pickup_day);
                $formattedDate = $carbonDate->toDateString();

                PickupDetails::create([
                    'order_id' => $order->id,
                    'first_name' => $request->first_name ?? null,
                    'last_name' => $request->last_name ?? null,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'day' => $formattedDate,
                    'time' => $request->pickup_time,
                ]);
            }

            if ($totals['discount'] && $totals['discount_type'] === 'company') {
                AppliedDiscount::create([
                    'order_id' => $order->id,
                    'source' => 'company',
                    'type' => 'percentage',
                    'amount' => $totals['discount_value'],
                    'target_sum' => $totals['subtotal'],
                    'net' => $totals['discount_net'],
                ]);
            }

            return $order;
        });

    }

    public function closeOrdersAndParents($orders): void
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
    }

    public function storeStaffOrder($request)
    {
        if ($request->service_method === Order::DINEIN) {
            $table = RestaurantTable::find($request->table);
        } else {
            $table = false;
        }

        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        if ($table) {
            if ($table->status == RestaurantTable::UNAVAILABLE) {
                return 'unavailable';
            }

            if ($table->restaurant->id !== $restaurant->id) {
                return 'something is not right';
            }
        }

        if (!$restaurant->valid_working_hours) {
            return 'unavailable';
        }

        $cartService = new StaffCartService();

        $cart = $cartService->getCart();

        if (!$cart) {
            return 'no items in the cart';
        }

        $totals = $cartService->getTotals();

        $order = DB::transaction(function () use ($request, $table, $restaurant, $cart, $totals) {
            $order = Order::create([
                'table_id' => $table?->id ?? null,
                'user_id' => auth()->user()->id,
                'take_away' => $request->service_method === Order::TAKEAWAY ? 'yes' : null,
                'service_method' => $request->service_method,
                'restaurant_id' => $restaurant->id,
                'amount' => isset($request->payment_method) ? $totals['gross'] : $totals['subtotal'],
                'tips' => isset($totals['tips']) && isset($request->payment_method) ? $totals['tips']['value'] : 0,
                'payment_method' => $request->payment_method ?? null,
                'status' => Order::INITIAL,
                'restaurant_status' => Order::INITIAL,
                'bar_status' => Order::INITIAL,
            ]);

            foreach ($cart as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'notes' => $item['notes'] ?? null,
                ]);

                if (isset($item['bundle'])) {
                    foreach ($item['bundle'] as $bundleItem) {
                        OrderItemBundle::create([
                            'order_item_id' => $orderItem->id,
                            'entity_id' => $bundleItem['entity_id'],
                            'entity_type' => $bundleItem['entity_type'] === 'products' ? Product::class : ($bundleItem['entity_type'] === 'extras' ? Extra::class : null),
                            'price' => $bundleItem['price'],
                        ]);
                    }
                }
            }

            if (isset($totals['manual_discount'])) {
                AppliedDiscount::create([
                    'order_id' => $order->id,
                    'source' => 'staff',
                    'type' => $totals['manual_discount']['type'],
                    'amount' => $totals['manual_discount']['amount'],
                    'target_sum' => $totals['subtotal'],
                    'net' => $totals['manual_discount']['net_amount'],
                ]);
            }

            return $order;
        });

        $this->checkGroupingStatus($order);

        return $order;
    }

    public function sendOffsiteOrderNotification(Order $order): void
    {
        $order->totals = $this->getTotals($order);

        $receipt = view('pdf.receipt', compact('order'));
        $pdf = Pdf::loadHTML($receipt);

        if ($order->service_method === Order::TAKEAWAY && $order->pickupDetails) {
            SendNewTakeawayOrderNotification::dispatch([
                'order' => $order,
                'totals' => $order->totals,
                'customer_email' => $order->pickupDetails->email,
                'restaurant_email' => $order->restaurant->email,
                'receipt' => base64_encode($pdf->output()),
            ]);
        } else {
            if ($order->service_method === Order::DELIVERY) {
                SendNewDeliveryOrderNotification::dispatch([
                    'order' => $order,
                    'totals' => $order->totals,
                    'customer_email' => $order->deliveryInformation->email,
                    'restaurant_email' => $order->restaurant->email,
                    'receipt' => base64_encode($pdf->output()),
                ]);
            }
        }
    }

    public function checkGroupingStatus(Order $order)
    {
        if (is_null($order->parent_id)) {
            if ($order->service_method === Order::DINEIN && is_null($order->display_id)) {
                $order->is_grouped = 1;
                $order->parent_id = $this->findParentOrder($order)->id;
            }

            if (restaurant_settings_get('group_order_delay',
                    $order->restaurant) && $order->is_grouped && !$order->user_id) {
                $newStatus = Order::GROUP;
            } elseif ($order->restaurant->hasPrinterForType([Product::RESTAURANT])) {
                $newStatus = Order::PREPARING;
            } else {
                $newStatus = Order::NEW;
            }

            $order->status = $newStatus;
            $order->restaurant_status = $newStatus;
            $order->bar_status = $newStatus;
        }

        if ($order->parent_id) {
            $parent = Order::find($order->parent_id);

            if (!empty($parent)) {
                $order->status = $parent->status;
                $order->restaurant_status = $parent->status;
                $order->bar_status = $parent->status;
            }

            // The payment method is displayed for every order on the Staff Dashboard.
            // If any child order's payment method is cash, the staff need to be informed.
            // Because the payment method set on the parent order is displayed, that has to be changed, too.
            // For the moment, it should only apply to orders placed from the customer app (i.e., no user_id)
            if (!$order->user_id && $order->payment_method == Order::CASH && $parent->payment_method == Order::ONLINE) {
                $parent->update(['payment_method' => Order::CASH]);
            }

        }

        $order->save();
    }

    /**
     * Initiate if online payment
     * @param Order $order
     * @return string $redirectUrl
     * @throws Exception
     */
    public function pay(Order $order): string
    {
        if ($order->payment_method !== Order::ONLINE) {
            $this->setSessionOrders($order->id);

            return route('customer.order.list');
        }

        //START ONLINE PAYMENT
        $paymentService = new PaymentService(PayrexxPaymentService::NAME);

        $paymentService->initiatePayment($order);

        return $paymentService->getApproveRedirectURL();
    }

    public function setSessionOrders(int $orderID, $session = false): void
    {
        if ($session) {
            session()->regenerate();

            Session::setId($session);

            Session::start();

        }

        $session = session();

        $order = Order::find($orderID);

        $session->put('orders.' . $order->id, $order->id);


        $this->checkGroupingStatus($order);

        $order->save();

        $session->forget('cart');
        $session->forget('quantity');
        $session->put('cart', []);
        $session->forget('notes');
        $session->forget("in-group");
    }

    public function update(OrderFormRequest $request): void
    {
        $order = Order::findOrFail($request->orderID);

        if ($request->orderStatus) {
            $this->updateAllOrderStatuses($order, $request->orderStatus);
        }
        if (isset($request->type) && isset($request->foodTypeId)) {
            $this->updateStatus($order, $request->type, $request->foodTypeId);
        }

        if ($request->type == 'bar') {
            $this->updateStatus($order, $request->type);
        }

        if ($this->isComplete($order) && $order->device_id && $order->table->type == RestaurantTable::OFFSITE) {
            NewOrderStatusNotification::dispatch($order->device_id, $order);
        }
    }

    public function updateAllOrderStatuses($order, $status): void
    {

        $order->update([
            'status' => $status,
            'restaurant_status' => $status,
            'bar_status' => $status
        ]);

        $order->foodStatuses()->update([
            'status' => $status,
        ]);

        if ($order->isParent) {
            $children = $order->children;
            foreach ($children as $child) {
                $child->update([
                    'status' => $status,
                    'restaurant_status' => $status,
                    'bar_status' => $status
                ]);
            }


            OrderFoodStatus::with('order')
                ->whereRelation('order', 'parent_id', $order->id)
                ->update([
                    'status' => $status
                ]);
        }
    }

    public function removeItem($items): JsonResponse
    {
        $items = collect($items);

        $productQty = $items->mapWithKeys(function ($item) {
            return [$item['product_id'] => $item['qty']];
        });

        $orderFirstItem = OrderItem::find($items->first()['order_item_id']);
        $order = Order::find($orderFirstItem['order_id']);
        $tableId = $order['table_id'];
        $parentOrdersToCheck = [];
        $closeTable = false;

        // Fetch relevant orders
        $orders = Order::where('table_id', $tableId)
            ->whereIn('status', [Order::NEW, Order::PREPARING, Order::READY])
            ->where('service_method', Order::DINEIN)
            ->whereNull('display_id')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No orders found for the specified table']);
        }

        // Process each order
        DB::transaction(function () use ($orders, $productQty, $tableId, &$parentOrdersToCheck) {
            foreach ($orders as $order) {
                foreach ($order->items as $item) {

                    $id = $this->buildKeyForCompositeItem($item, false);

                    if($item->products->is_custom){
                        $id = $item->products->id . '_' . $item->price;
                    }

                    // Check if the product_id is in the removal list
                    if (isset($productQty[$id])) {
                        $qtyToRemove = $productQty[$id];
                        $pricePerUnit = $item->price;

                        // Check if the item has a partial payment
                        if ($item->paid_quantity) {
                            $paidQty = $item->paid_quantity;
                            $availableQty = $item->quantity - $paidQty;
                        } else {
                            $availableQty = $item->quantity;
                        }

                        // Determine the quantity to actually remove
                        $qtyToAdjust = min($qtyToRemove, $availableQty);

                        // Adjust the quantity
                        $item->quantity -= $qtyToAdjust;
                        $order->amount -= $qtyToAdjust * $pricePerUnit;

                        if ($item->quantity <= 0) {
                            $item->delete(); // Soft delete the item
                        } else {
                            $item->save();
                        }

                        // Update quantity to remove
                        $productQty[$id] -= $qtyToAdjust;

                        // If item was partially paid, and we removed more than paid quantity
                        if ($item->paid_quantity && $productQty[$id] <= 0) {
                            $item->status = OrderItem::PAID;
                            $item->save();
                        }

                        // Break the loop if all qty for the product has been handled
                        if ($productQty[$id] <= 0) {
                           // continue;
                        }
                    }
                }

                // Save updated total
                $order->save();

                $getOrder = Order::find($order->id);

                // Check if order has no items left
                if ($getOrder->items->count() == 0) {
                    $getOrder->status = Order::CANCELED; // Cancel the order
                    $getOrder->save();

                    // Collect parent orders to check
                    if ($getOrder->parent_id) {
                        $parentOrdersToCheck[] = $getOrder->parent_id;
                    }
                }
            }
        });

        // Handle parent orders cancellation and table availability check
        foreach (array_unique($parentOrdersToCheck) as $parentOrderId) {
            $parentOrder = Order::find($parentOrderId);
            if ($parentOrder) {
                $childOrdersCount = Order::where('parent_id', $parentOrderId)
                    ->whereIn('status', [Order::NEW, Order::PREPARING])
                    ->where('table_id', $tableId)
                    ->count();

                if ($childOrdersCount == 0) {
                    $parentOrder->status = Order::CANCELED;
                    $parentOrder->save();

                    // After canceling the parent order, check if the table should be made available
                    $remainingChildOrders = Order::where('parent_id', $parentOrderId)
                        ->whereIn('status', [Order::NEW, Order::PREPARING])
                        ->where('table_id', $tableId)
                        ->count();

                    if ($remainingChildOrders == 0) {
                        $table = RestaurantTable::find($parentOrder->table_id);
                        if ($table) {
                            $table->status = RestaurantTable::AVAILABLE;
                            $table->save();
                            $closeTable = true;
                        }
                    }
                }
            }
        }


        return response()->json([
            "success" => true,
            "message" => "The items have been removed",
            'close_table' => $closeTable
        ]);
    }

    /**
     * Update the status of an order (restaurant_status or bar_status)
     *
     * @param Order $order
     * @param string $type
     * @param int foodTypeId
     * @return void
     */
    public function updateStatus(Order $order, string $type, int $foodTypeId = null)
    {
        $order->refresh();

        $newTypeStatus = null;
        $foodTypeStatus = null;

        if ($type == 'bar') {
            $status = $order->bar_status;
        }

        if ($type == 'restaurant' && $foodTypeId) {
            $foodTypeStatus = $order->foodStatuses()->where('food_type_id', $foodTypeId)->first();

            $status = $foodTypeStatus->status;
        }


        if ($status == Order::NEW) {
            $newTypeStatus = Order::PREPARING;
        }

        if ($status == Order::PREPARING) {
            $newTypeStatus = Order::READY;
        }

        // Reverse the order back in preparing
        if ($status == Order::READY && !$newTypeStatus) {
            $newTypeStatus = Order::PREPARING;
        }

        if ($newTypeStatus) {
            if ($foodTypeStatus) {
                $foodTypeStatus->status = $newTypeStatus;
                $foodTypeStatus->save();
            }

            if ($type == 'bar') {
                $order->bar_status = $newTypeStatus;
            }

            $order->refreshStatuses();
        }
    }

    public function isComplete(Order $order): bool
    {
        $order->refresh();

        if ($order->status != Order::READY) {
            return false;
        }

        return true;
    }

    public function sendReceipt($email, ?string $order = null)
    {
        $attachments = [];
        $to_remove = [];

        if ($order) {
            $orders[] = Order::find($order);
        } else {
            $orders = $this->orders();
        }

        foreach ($orders as $order) {
            $order->totals = $this->getTotals($order);
        }

        //Generate PDF foreach order
        $ordersView = '';
        foreach ($orders as $key => $order) {
            $ordersView .= view('pdf.receipt', compact('order'));
        }
        $pdf = Pdf::loadHTML($ordersView);

        if (\request()->type == 'download') {
            return $pdf;
        }

        NewReceiptNotification::dispatch($orders, base64_encode($pdf->output()), $email);
    }

    public function checkOrderExists()
    {
        if (!session()->has('orders') && !session()->has('table.url')) {
            abort(404);
        }

        if (session()->has('table.url') && !session()->has('orders')) {
            return ['orders' => [], 'url' => session('table.url')];
        }
        $url = null;
        $canceled = null;

        $sessionOrders = session()->get('orders');

        $orders = Order::find($sessionOrders);
        $ordersID = $orders->map(function ($order) {
            return $order->id;
        })->toArray();

        foreach ($sessionOrders as $orderID) {
            // Send notification when order gets canceled
            $order = $orders->find($orderID);

            if (empty($order->status)) {
                session()->forget("orders.$orderID");
                continue;
            }

            if ($order->status == Order::CLOSED) {
                session()->forget("orders.$orderID");
            }

            if ($order->status == Order::CANCELED) {
                $canceled = number_format($order->display_id);
            }
            if (!in_array($orderID, $ordersID) || !in_array($order->status, Order::STAFFSTATUS)) {
                session()->forget("orders.$orderID");
            }
        }
        if ($orders->isEmpty()) {
            $url = session('table.url');
        }

        if (empty(session()->get('orders'))) {
            $url = session('table.url');
        }

        return ['orders' => $orders, 'url' => $url, 'canceled' => $canceled];
    }

    public static function activeOrders()
    {
        $orders = Order::query()->whereNull('parent_id');

        $orders->with(['table', 'items', 'deliveryInformation', 'pickupDetails'])->whereIn('status',
            Order::STAFFSTATUS);

        return $orders->get();
    }

    public static function closedOrders()
    {

        $orders = Order::with(['table', 'items', 'deliveryInformation', 'pickupDetails'])->where('status',
            Order::CLOSED)->orderBy('updated_at',
            'DESC');

        // Get orders from current working schedule
        // $restaurant = Restaurant::find(request()->user()->restaurant_id);

        // $orders->whereBetween('updated_at', $restaurant->getWorkingSchedule());

        $orders->where('updated_at', '>', now()->subHours(2));
        $orders->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('parent_id', null);
                $query2->where('is_grouped', 1);
            })->orWhere('is_grouped', null);
        });

        return $orders->get();
    }

    public function groupByStatus($orders): array
    {
        $ready = $preparing = $new = [];

        foreach ($orders as $order) {
            $status = $order->status;

            switch ($status) {
                case Order::PREPARING:
                    $preparing[] = $order;
                    break;
                case Order::READY:
                    $ready[] = $order;
                    break;
                case Order::GROUP:
                case Order::NEW:
                    $new[] = $order;
            }
        }

        return compact('new', 'preparing', 'ready');
    }

    /** Get all the orders a client has in this current session
     * Along with the items and products.
     * @return Collection
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCurrentOrders()
    {
        return Order::whereIn('id', session()->get('orders') ?? [])->where('restaurant_id',
            session()->get('restaurant.id', 0))->with('items.products')->get();
    }

    public function payCash(Order $order)
    {
        $order->update([
            'payment_method' => Order::CASH
        ]);
    }

    public function tipList($request = null)
    {
        $startDate = Carbon::now()->startOfMonth()->startOfDay();
        $endDate = Carbon::now();

        if ($request) {
            $startDate = Carbon::createFromFormat('Y/m/d H:i', $request->input('startDate'));
            $endDate = Carbon::createFromFormat('Y/m/d H:i', $request->input('endDate'));
        }

        $tables = RestaurantTable::with('orders')->get();

        $totalTips = 0;

        $tablesData = [];

        foreach ($tables as $table) {
            $tablesData[$table->id] = [
                'name' => $table->name,
                'tips' => $table->orders
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotIn('status', [Order::INITIAL, Order::CANCELED])
                    ->sum('tips')
            ];

            $totalTips += $tablesData[$table->id]['tips'];
        }

        return compact("tablesData", "totalTips");
    }

    /**
     * If user has not explicitly refused grouping, then search for a parent order or create one
     *
     * @param Order $order
     * @return Order|null
     */
    public function findParentOrder(Order $order): Order|null
    {

        $hasDelay = restaurant_settings_get('group_order_delay', $order->restaurant) ? true : false;

        $hasRestaurantPrinter = $order->restaurant->hasPrinterForType([Product::RESTAURANT]);

        $statuses = [];

        if (!$order->user_id) {
            $statuses[] = Order::GROUP;
        }

        if (!$hasRestaurantPrinter) {
            $statuses[] = Order::NEW;
        }

        // If there isn't a delay set, search for orders with the status new
        $parentOrder = Order::whereIn('status', $statuses)
            ->where('restaurant_id', $order->restaurant_id)
            ->where('table_id', $order->table_id)
            ->where('is_grouped', 1)
            ->whereNull('parent_id')
            ->when($order->user_id !== null, function ($query) use ($order) {
                return $query->where('user_id', $order->user_id);
            })
            ->when(!$order->user_id, function ($query) {
                return $query->where('user_id', null);
            })
            ->first();

        if (!$parentOrder) {

            if (!$order->user_id && $hasDelay) {
                $status = Order::GROUP;
            } elseif ($hasRestaurantPrinter) {
                $status = Order::PREPARING;
            } else {
                $status = Order::NEW;
            }

            $parentOrder = $this->createParentOrder($order, $status);
        }

        return $parentOrder;
    }

    /**
     * Create new parent order
     *
     * @param Order $order
     * @param string $initialStatus
     * @return Order
     */
    public function createParentOrder(Order $order, string $initialStatus): Order
    {
        $parent = new Order;

        $parent->status = $initialStatus;
        $parent->restaurant_id = $order->restaurant_id;
        $parent->table_id = $order->table_id;

        if ($order->user_id) {
            $parent->user_id = $order->user_id;
        }

        $parent->service_method = $order->service_method;

        $parent->is_grouped = 1;
        $parent->amount = 0;
        $parent->payment_method = $order->payment_method;
        $parent->restaurant_status = $initialStatus;
        $parent->bar_status = $initialStatus;
        $parent->save();

        $parent->generateDisplayId();

        return $parent;
    }

    public function getTotals(Order $order)
    {
        $settings = (new SettingService)->getSettings();

        $vat = in_array($order->service_method,
            [Order::TAKEAWAY, Order::DELIVERY]) ? (float)$settings['takeaway_vat'] : (float)$settings['vat'];

        $totals['currency'] = __('labels.currency');
        $totals['vat']['amount'] = $vat;
        $totals['gross'] = priceFormat($order->amount);
        $totals['amount_due'] = $totals['gross'];
        $totals['net'] = $vat > 0 ? priceFormat($totals['gross'] / (1 + $vat / 100)) : priceFormat($totals['gross']);
        $totals['vat']['net'] = priceFormat($totals['gross'] - $totals['net']);
        $totals['subtotal'] = priceFormat($this->calculateSubtotalAmount($order));
        $totals['discounts']['total'] = 0;

        if ($order->discount_takeaway === null) {
            foreach ($order->discounts as $appliedDiscount) {
                $totals['discounts']['total'] += $appliedDiscount->net;
            }
        } else {
            $takeawayDiscount = (float)$order->discount_takeaway;

            if ($takeawayDiscount > 0) {
                $totals['discounts']['total'] = $totals['subtotal'] - $order->amount;
            }
        }

        if ($totals['discounts']['total'] > 0) {
            $totals['discounts']['total'] = priceFormat($totals['discounts']['total']);
        }

        if ($order->tips > 0) {
            $totals['tips']['value'] = priceFormat($order->tips);
        }

        if (isset($totals['tips'])) {
            $totals['amount_due'] = priceFormat($totals['amount_due'] + $totals['tips']['value']);
        }

        if ($order->delivery_fee > 0) {
            $totals['delivery_fee']['value'] = priceFormat($order->delivery_fee);
            $totals['amount_due'] = priceFormat($totals['amount_due'] + $order->delivery_fee);
        }

        return $totals;
    }

    public function getOrderItems(Order $order)
    {
        $products = [];
        $items = $order->items;

        foreach ($items as $item) {
            $products[] = [
                'name' => $item->products->name,
                'quantity' => $item->quantity,
                'price' => (float)$item->price,
            ];

            if ($item->itemBundles->count() > 0) {
                foreach ($item->itemBundles as $bundledItem) {
                    $products[] = [
                        'name' => $bundledItem->entity->name ?? $bundledItem->entity->title,
                        'quantity' => $item->quantity,
                        'price' => (float)$bundledItem->price,
                    ];
                }
            }
        }

        return $products;
    }

    public function getOrderItemsByType(Order $order, $type)
    {
        $groupedItems = null;

        if ($type === Product::RESTAURANT) {
            $orderItems = OrderItem::with(['products', 'products.foodType', 'itemBundles'])
                ->whereRelation('products', 'type', $type)
                ->whereRelation('order', 'id', $order->id)
                ->get();

            $groupedItems = $orderItems->groupBy(function ($item) {
                return optional($item->products->foodType)->name;
            });
        }

        if ($type === Product::BAR) {
            $orderItems = OrderItem::with(['products', 'products.categories', 'itemBundles'])
                ->whereRelation('products', 'type', $type)
                ->whereRelation('order', 'id', $order->id)
                ->get();

            $groupedItems = $orderItems->groupBy(function ($item) {
                return optional($item->products->categories->first())->name;
            });
        }

        if ($groupedItems === null) {
            return null;
        }

        $products = [];

        foreach ($groupedItems as $taxonomy => $items) {
            $products[$taxonomy] = [];
            foreach ($items as $item) {
                $product = [
                    'name' => $item->products->name,
                    'quantity' => $item->quantity,
                    'price' => (float)$item->price,
                    'notes' => $item->notes
                ];

                if ($item->itemBundles->count() > 0) {
                    $product['name'] .= ' (Extras: ';

                    foreach ($item->itemBundles as $bundledItem) {
                        $product['name'] .= $bundledItem->entity->name ?? $bundledItem->entity->title;

                        if ($bundledItem !== $item->itemBundles->last()) {
                            $product['name'] .= ', ';
                        }
                    }

                    $product['name'] .= ')';
                }

                $products[$taxonomy][] = $product;
            }
        }

        return $products;
    }

    public function getGroupedOrderItems(Order $order, $type = null): ?array
    {
        $groupedItems = null;

        if ($type === Product::RESTAURANT) {
            $orderItems = OrderItem::with(['products', 'products.foodType', 'itemBundles'])
                ->whereRelation('products', 'type', $type)
                ->whereRelation('order', 'parent_id', $order->id)
                ->get();

            $groupedItems = $orderItems->groupBy(function ($item) {
                return optional($item->products->foodType)->name;
            });
        }

        if ($type === Product::BAR) {
            $orderItems = OrderItem::with(['products', 'products.categories', 'itemBundles'])
                ->whereRelation('products', 'type', $type)
                ->whereRelation('order', 'parent_id', $order->id)
                ->get();

            $groupedItems = $orderItems->groupBy(function ($item) {
                return optional($item->products->categories->first())->name;
            });
        }

        if ($groupedItems === null) {
            return null;
        }

        $products = [];

        foreach ($groupedItems as $taxonomy => $items) {
            $products[$taxonomy] = [];
            foreach ($items as $item) {
                $product = [
                    'name' => $item->products->name,
                    'quantity' => $item->quantity,
                    'price' => (float)$item->price,
                    'notes' => $item->notes
                ];

                if ($item->itemBundles->count() > 0) {
                    $product['name'] .= ' (Extras: ';

                    foreach ($item->itemBundles as $bundledItem) {
                        $product['name'] .= $bundledItem->entity->name ?? $bundledItem->entity->title;

                        if ($bundledItem !== $item->itemBundles->last()) {
                            $product['name'] .= ', ';
                        }
                    }

                    $product['name'] .= ')';
                }

                $products[$taxonomy][] = $product;
            }
        }

        return $products;
    }

    public function calculateSubtotalAmount(Order $order): float|int
    {
        if ($order->isParent) {
            // To Do
            return 1000000;
        }

        $subtotal = 0;

        foreach ($order->items as $item) {
            if (!$item->status) {
                $subtotal += (float)$item->price * $item->quantity;
            }

            if ($item->itemBundles->count() > 0) {
                foreach ($item->itemBundles as $bundledItem) {
                    $subtotal += (float)$bundledItem->price * $item->quantity;
                }
            }
        }

        return $subtotal;
    }

    public function mergePartialOrders($orders, $products, $request = []): array
    {
        if (empty($request['discount']) && session()->has('table_payment_options')) {
            $request['discount'] = session()->get('table_payment_options')['discount'];
        }

        $totals = $this->mergeOrders($orders, false, false, $request);

        $newItems = [];
        $subtotal = 0;

        foreach ($totals['items'] as $category => $subcategories) {

            foreach ($subcategories as $subcategory => $items) {
                foreach ($items as $k => $item) {
                    if (array_key_exists($k, $products)) {
                        // Update quantity
                        $item['quantity'] = $products[$k]['qty'];
                        // Add to new items array
                        $newItems[$category][$subcategory][$k] = $item;
                        // Update subtotal
                        $subtotal += $item['quantity'] * $item['price'];

                        if ($item['bundleItems']) {
                            foreach ($item['bundleItems'] as $bundleItem) {
                                $subtotal += $item['quantity'] * $bundleItem['price'];
                            }
                        }
                    }
                }
            }
        }

        $amountDue = $subtotal;

        //Calculate Discount
        if (!empty($request['discount'])) {
            $totals['discounts']['breakdown'][0] = [
                'order' => 0,
                'target_sum' => $subtotal,
            ];

            if ($request['discount']['type'] == 'percentage') {
                $discountNet = $subtotal * ($request['discount']['amount'] / 100);

                $amountDue -= $discountNet;

                $totals['discounts']['breakdown'][0] = [
                    'net' => $discountNet,
                ];

                $totals['discounts']['total'] = $discountNet;
            } else {
                $discountNet = $request['discount']['amount'];
                $amountDue -= $discountNet;
            }

            $totals['totals']['manual_discount'] = $this->calculateManualDiscount($request['discount'], $subtotal);
            $totals['totals']['discounts']['total'] = priceFormat($totals['totals']['manual_discount']['net_amount']);

        }

        // Calculate VAT
        $vatRate = $totals['totals']['vat']['amount'] / 100;
        $vatAmount = $amountDue * $vatRate;
        $netAmount = $amountDue - $vatAmount;

        //Calculate Tips
        if (!empty($request['tips']) || !empty($request['partial_pay_id'])) {

            if ($request['partial_pay_id']) {
                $request['tips'] = PartialPayment::find($request['partial_pay_id'])['tips'];
            }

            if ($request['tips']) {
                $totals['totals']['tips'] = [
                    'value' => priceFormat($request['tips']),
                    'suffix' => strtolower(__('labels.tips')),
                ];
            }
        } else {
            $request['tips'] = 0;
        }

        // Update totals in the response
        $totals['items'] = $newItems;
        $totals['totals']['subtotal'] = number_format($subtotal, 2);
        $totals['totals']['vat']['net'] = number_format($vatAmount, 2);
        $totals['totals']['gross'] = number_format($amountDue, 2);
        $totals['totals']['amount_due'] = number_format(($amountDue + $request['tips']), 2);
        $totals['totals']['net'] = number_format($netAmount, 2);

        return $totals;
    }

    public function mergeOrders($orders, $checkout = false, $includeOnlinePayment = false, $request = []): array
    {

        $firstOrder = $orders->first();

        // At the moment, the application allows only one discount to be applied
        $firstOrderDiscount = $firstOrder->discounts()->first();

        $result = [
            'orders' => $orders->pluck('id')->toArray(),
            'closable' => true,
            'datetime' => [
                'date' => $firstOrder->created_at->format('Y-m-d'),
                'time' => $firstOrder->created_at->format('H:i'),
            ],
            'table' => $firstOrder->table_id,
            'number' => $firstOrder->isParent ? $firstOrder->display_id : $firstOrder->parent->display_id,
        ];

        $mergedReceipt = [];

        $totals = [
            'currency' => __('labels.currency'),
            'subtotal' => 0,
            'discounts' => [
                'total' => 0,
                'breakdown' => [],
            ],
        ];

        if ($firstOrderDiscount) {
            $totals['discounts']['type'] = $firstOrderDiscount->type;
            $totals['discounts']['amount'] = $firstOrderDiscount->amount;
        }

        $combinedItems = [];
        $combinedItems[Product::RESTAURANT] = [];
        $combinedItems[Product::BAR] = [];

        $tips = 0;

        foreach ($orders as $order) {
            if ($order->status !== Order::READY) {
                $result['closable'] = false;
            }

            if ($order->payment_method === Order::ONLINE) {
                $result['orders_breakdown'][] = [
                    'payment' => 'online',
                    'orders' => [$order->id],
                ];
            } else {
                $mergedReceipt[] = $order->id;
            }

            foreach ($order->items as $item) {

                $qty = $item->quantity - $item->paid_quantity;

                if ($item->status == OrderItem::PAID && !isset($request->partial)) {
                    // continue;
                    $qty = $item->paid_quantity;
                }

                $processedItem = [
                    'id' => $item->products->id,
                    'restaurant_id' => $order->restaurant_id,
                    'table_id' => $order->table_id,
                    'order_item_id' => $item->id,
                    'order_id' => $item->order_id,
                    'name' => $item->products->name,
                    'quantity' => $qty,
                    'price' => priceFormat($item->price),
                    'notes' => $item->notes,
                    'online_payment' => $order->payment_method === Order::ONLINE || ($item->status == OrderItem::PAID && !isset($request->partial)),
                    'bundleItems' => [],
                ];

                // online payment needs to be excluded from payment totals, but included in receipts
                if ($includeOnlinePayment || $order->payment_method !== Order::ONLINE) {
                    if (!($item->status == OrderItem::PAID && !isset($request->partial))) {
                        $totals['subtotal'] += $processedItem['quantity'] * $processedItem['price'];
                    }
                }

                foreach ($item->itemBundles as $bundleItem) {
                    $processedBundleItem = [
                        'name' => $bundleItem->entity?->name ?? $bundleItem->entity?->title,
                        'price' => priceFormat($bundleItem->price),
                    ];

                    $processedItem['bundleItems'][] = $processedBundleItem;

                    // online payment needs to be excluded from payment totals, but included in receipts
                    if ($includeOnlinePayment || $order->payment_method !== Order::ONLINE) {
                        $totals['subtotal'] += $processedItem['quantity'] * $processedBundleItem['price'];
                    }
                }

                $taxonomy = $item->products->type === Product::RESTAURANT ? $item->products->foodType->name : $item->products->categories->first()->name;

                $key = $item->itemBundles->count() ? $this->buildKeyForCompositeItem($item,
                    $processedItem['online_payment']) : $item->products->id;

                if($item->products->is_custom){
                    $key = $item->products->id . '_' . $item->price;
                }

                $key .= $processedItem['online_payment'] ? '_paid' : '';
                if (isset($combinedItems[$item->products->type][$taxonomy][$key])) {
                    $combinedItems[$item->products->type][$taxonomy][$key]['quantity'] += $processedItem['quantity'];
                } else {
                    $combinedItems[$item->products->type][$taxonomy][$key] = $processedItem;
                }
            }

            // online payment needs to be excluded from payment totals, but included in receipts
            if ($includeOnlinePayment || $order->payment_method !== Order::ONLINE) {
                $tips += $order->tips;

                if ($order->discount_takeaway === null) {
                    foreach ($order->discounts as $discount) {
                        $totals['discounts']['breakdown'][] = [
                            'order' => $order->id,
                            'target_sum' => $discount->target_sum,
                            'net' => $discount->net,
                        ];

                        $totals['discounts']['total'] += $discount->net;
                    }
                } else {
                    $takeawayDiscount = (float)$order->discount_takeaway;

                    if ($takeawayDiscount > 0) {
                        $totals['discounts']['total'] = $totals['subtotal'] - $order->amount;
                    }
                }
            }
        }

        if (count($mergedReceipt)) {
            $result['orders_breakdown'][] = [
                'payment' => 'merged',
                'orders' => $mergedReceipt,
            ];
        }

        $result['items'] = $combinedItems;

        if ($tips > 0) {
            $totals['tips'] = [
                'value' => priceFormat($tips),
                'suffix' => strtolower(__('labels.tips')),
            ];
        }

        if ($checkout) {
            $tablePaymentOptions = session()->get('table_payment_options');

            if (isset($tablePaymentOptions['tips'])) {
                $totals['tips'] = [
                    'value' => priceFormat($tablePaymentOptions['tips']),
                    'suffix' => strtolower(__('labels.tips')),
                ];
            }

            if (isset($tablePaymentOptions['discount'])) {
                $totals['manual_discount'] = $this->calculateManualDiscount($tablePaymentOptions['discount'],
                    $totals['subtotal']);
                $totals['discounts']['total'] = priceFormat($totals['manual_discount']['net_amount']);
            }
        }

        $result['totals'] = $this->processTotals($totals);

        return $result;
    }

    private function buildKeyForCompositeItem($item, $paid)
    {
        $result = $item->products->id;

        foreach ($item->itemBundles as $bundle) {
            $type = $bundle->entity_type === Product::class ? 'products' : 'extras';

            // The key also needs to include the price because the application allows adding
            // the same Product/Extra twice in the Bundle (in separate Groups), and they can have
            // different prices. Another solution is saving the bundle_id (i.e., group id) in the
            // order_item_bundles table, and use that in the key
            $result .= '_' . $type . '_' . $bundle->entity_id . '_' . $bundle->price;
        }

        if ($paid) {
            $result .= '_paid';
        }

        return $result;
    }

    private function processTotals($totals)
    {


        $settings = (new SettingService)->getSettings();

        $vat = (float)$settings['vat'];

        $totals['vat']['amount'] = $vat;

        $totals['subtotal'] = priceFormat($totals['subtotal']);

        $totals['gross'] = priceFormat($totals['subtotal'] - $totals['discounts']['total']);

        $totals['amount_due'] = $totals['gross'];

        $totals['net'] = $vat > 0 ? priceFormat($totals['gross'] / (1 + $vat / 100)) : $totals['gross'];

        $totals['vat']['net'] = priceFormat($totals['gross'] - $totals['net']);


        if (isset($totals['tips'])) {
            $totals['amount_due'] = priceFormat($totals['amount_due'] + $totals['tips']['value']);
        }

        return $totals;
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
}
