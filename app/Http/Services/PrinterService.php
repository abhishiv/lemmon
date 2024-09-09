<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Product;
use App\Models\RestaurantTable;
use Carbon\Carbon;

class PrinterService
{
    public function __construct(
        private OrderService $orderService,
    ) {
    }

    public function getReceipts($jobs)
    {
        $table = RestaurantTable::find($jobs[0]['table']);

        $restaurant = $table->restaurant;

        $result = [
            'settings' => $this->getPrintSettings($restaurant),
            'printer' => $restaurant->getPrinter(Restaurant::RECEIPT),
            'orders' => [],
        ];

        foreach ($jobs as $job) {
            $result['orders'][] = [
                'id' => substr(md5('receipt-' . implode('-', $job['orders'])), 0, 30),
                'ticket' => $this->buildReceiptObjectImproved($job, $table),
            ];
        }

        return $result;
    }

    private function buildReceiptObjectImproved($job, $table): array
    {

        $firstOrder = Order::find($job['orders'][0]);

        return [
            'print_type' => 'receipt',
            'datetime' => $this->getLocalCurrentDateTime(),
            'order_number' => [
                'display_name' => __('staff/printer.order') . ' #',
                'value' => $job['number'],
            ],
            'table' => [
                'display_name' => __('staff/printer.table'),
                'value' => $table->name ?? '-',
            ],
            'payment_method' => [
                'display_name' => __('staff/printer.payment'),
                'value' => $firstOrder->payment_method ? __('staff/printer.' . $firstOrder->payment_method) : '-',
            ],
            'totals' => $job['totals'],
            'items' => $this->getOnlyItems($job['items']),
            'takeaway' => false,
        ];
    }

    private function getOnlyItems($items)
    {
        $result = [];

        foreach ($items as $productType) {
            foreach ($productType as $taxonomy) {
                foreach ($taxonomy as $item) {
                    $result[] = $item;

                    foreach ($item['bundleItems'] as $bundleItem) {
                        $result[] = [
                            'name' => $bundleItem['name'],
                            'price' => $bundleItem['price'],
                            'quantity' => $item['quantity']
                        ];
                    }
                }
            }
        }

        return $result;
    }

    public function getReceiptPrintObject(Order $order, $onlyCash = false)
    {
        return [
            'settings' => $this->getPrintSettings($order->restaurant),
            'printer' => $order->restaurant->getPrinter(Restaurant::RECEIPT),
            'orders' => $this->getReceiptsForOrder($order, $onlyCash),
        ];
    }

    private function getReceiptsForOrder(Order $order, $onlyCash)
    {
        $receipts = [];

        if ($order->isParent) {
            foreach ($order->children as $childOrder) {
                if ($onlyCash && $childOrder->payment_method !== Order::CASH) {
                    continue;
                }

                $orderInformation = $this->getReceiptInformation($childOrder);

                if (!$orderInformation) {
                    continue;
                }

                $receipts[] = [
                    'id' => "receipt-{$childOrder->id}",
                    'ticket' => $orderInformation,
                ];
            }
        } else {
            $orderInformation = ($onlyCash && $order->payment_method !== Order::CASH) ? false : $this->getReceiptInformation($order);

            if ($orderInformation !== false) {
                $receipts[] = [
                    'id' => "receipt-{$order->id}",
                    'ticket' => $orderInformation,
                ];
            }
        }

        return $receipts;
    }

    private function getReceiptInformation(Order $order)
    {
        $orderItems = $this->orderService->getOrderItems($order);

        if ($orderItems === null || count($orderItems) === 0) {
            return false;
        }

        return $this->buildReceiptObject($order, $orderItems);
    }

    private function buildReceiptObject(Order $order, $orderItems)
    {
        return [
            'print_type' => 'receipt',
            'datetime' => $this->getLocalCurrentDateTime(),
            'order_number' => [
                'display_name' => __('staff/printer.order') . ' #',
                'value' => $order->getDisplayId(),
            ],
            'table' => [
                'display_name' => __('staff/printer.table'),
                'value' => $order->table?->name ?? '-',
            ],
            'payment_method' => [
                'display_name' => __('staff/printer.payment'),
                'value' => $order->payment_method ? __('staff/printer.' . $order->payment_method) : '-',
            ],
            'delivery_info' => $this->getDeliveryInformation($order),
            'takeaway_info' => $this->getTakeAwayInformation($order),
            'totals' => $this->orderService->getTotals($order),
            'items' => $orderItems,
            'takeaway' => $order->take_away,
        ];
    }

    public function getAllOrders(): array
    {
        $restaurant = Restaurant::find(auth()->user()->restaurant_id);

        return [
            'settings' => $this->getPrintSettings($restaurant),
            'jobs_by_printer' => $this->buildObjectsForEachPrinter($restaurant),
            'print_receipt_url' => $restaurant->hasPrinterForType([Restaurant::RECEIPT]) ? route('staff.order.print.receipt') : '',
        ];
    }

    public function buildObjectsForEachPrinter(Restaurant $restaurant): array
    {
        $result = [];

        $targetPrintableTypes = [Product::BAR, Product::RESTAURANT];
        $availablePrintersTypes = $restaurant->getAvailablePrinterTypes();

        foreach ($targetPrintableTypes as $type) {
            if ($availablePrintersTypes[$type]) {
                $orders = $this->getOrderObjects($type);

                if (count($orders)) {
                    $result[] = [
                        'printer' => $restaurant->getPrinter($type),
                        'orders' => $orders,
                    ];
                }
            }
        }

        return $result;
    }

    public function getOrderObjects($type): array
    {
        $ordersToPrint = [];

        $newOrders = Order::whereIn('status', [Order::NEW, Order::PREPARING])
            ->whereNull('parent_id')
            ->get();

        $eligibleOrders = $newOrders->filter(function ($item) use ($type) {
            return $this->isEligible($item, $type);
        });

        foreach ($eligibleOrders as $order) {
            $ticket = $this->getTicket($order, $type);

            if ($ticket) {
                $ordersToPrint[] = [
                    'id' => "{$type}-{$order->id}",
                    'update_url' => route('staff.order.print.update', ['order' => $order->id]),
                    'ticket' => $ticket,
                ];
                //This is only for All'angolo becasue they have a small kitchen and they need double ticket for kitchen
                if($type == Product::RESTAURANT && auth()->user()->restaurant_id == 14){
                    $ordersToPrint[] = [
                        'id' => "{$type}-{$order->id}sd",
                        'update_url' => route('staff.order.print.update', ['order' => $order->id]),
                        'ticket' => $ticket,
                    ];
                }
            }
        }

        return $ordersToPrint;
    }

    public function getTicket(Order $order, $type): bool|array
    {
        $ticket = $this->getGroupedOrders($order, $type);

        if (!$ticket) {
            $this->setOrderStatus($order, $type, Order::NOTHINGTOPRINT);
            return false;
        }

        $this->setOrderStatus($order, $type, Order::PENDING);

        return $ticket;
    }

    private function getGroupedOrders(Order $order, $type)
    {
        if ($order->isParent) {
            $orderInformation = $this->getInformationForGroupedOrders($order, $type);
        } else {
            $orderInformation = $this->getOrderInformation($order, $type);
        }

        return $orderInformation;
    }

    private function getOrderInformation(Order $order, $type): bool|array
    {
        $orderItems = $this->orderService->getOrderItemsByType($order, $type);

        if ($orderItems === null || count($orderItems) === 0) {
            return false;
        }

        return $this->buildTicketObject($order, $orderItems, $type);
    }

    private function getInformationForGroupedOrders(Order $order, $type): bool|array
    {
        $orderItems = $this->orderService->getGroupedOrderItems($order, $type);

        if ($orderItems === null || count($orderItems) === 0) {
            return false;
        }

        return $this->buildTicketObject($order, $orderItems, $type);
    }

    private function buildTicketObject(Order $order, $orderItems, $type): array
    {
        return [
            'print_type' => 'ticket',
            'delivery_info' => $this->getDeliveryInformation($order),
            'takeaway_info' => $this->getTakeAwayInformation($order),
            'product_type' => $type,
            'datetime' => $this->getLocalCurrentDateTime(),
            'order_number' => [
                'display_name' => __('staff/printer.order') . ' #',
                'value' => $order->getDisplayId(),
            ],
            'table' => [
                'display_name' => __('staff/printer.table'),
                'value' => $order->table?->name ?? '-',
            ],
            'payment_method' => [
                'display_name' => __('staff/printer.payment'),
                'value' => __('staff/printer.' . $order->payment_method),
            ],
            'totals' => $this->orderService->getTotals($order),
            'items' => $orderItems,
            'takeaway' => $order->take_away,
        ];
    }

    private function getPrintSettings($restaurant)
    {
        return [
            'restaurant' => $this->getRestaurantInformation($restaurant),
            'labels' => $this->getPrinterLabels(),
            'messages' => $this->getPrinterMessages(),
            'status' => [
                'success' => Order::PRINTED,
                'failed' => Order::NOTPRINTED,
            ],
        ];
    }

    private function getRestaurantInformation($restaurant)
    {
        $logo = $restaurant->getLogo();

        return [
            'name' => $restaurant->name,
            'logo' => $logo['path'] ?? null,
            'address' => $restaurant->address,
            'phone' => $restaurant->receipt_phone,
            'company_registration' => $restaurant->company_registration,
            'message' => $restaurant->receipt_message
        ];
    }

    private function getLocalCurrentDateTime()
    {
        $datetime = Carbon::now('Europe/Zurich');

        return [
            'date' => $datetime->format('Y-m-d'),
            'time' => $datetime->format('H:i')
        ];
    }

    private function getPrinterLabels()
    {
        return [
            'quantity' => __('staff/printer.quantity'),
            'description' => __('staff/printer.description'),
            'total' => __('staff/printer.total'),
            'vat' => __('staff/printer.vat'),
            'net' => __('staff/printer.net'),
            'gross' => __('staff/printer.gross'),
            'vat_rate' => __('staff/printer.vat-rate'),
            'discount' => __('staff/printer.discount'),
            'tips' => __('staff/printer.tips'),
            'vat_id' => __('staff/printer.vat-id'),
            'bar' => __('staff/printer.bar'),
            'restaurant' => __('staff/printer.restaurant'),
            'order' => __('staff/printer.order'),
            'table' => __('staff/printer.table'),
            'notes' => __('staff/printer.notes'),
            'delivery' => __('labels.delivery-fr'),
            'delivery_address' => __('labels.delivery-address-fr'),
            'phone' => __('labels.phone-fr'),
            'name' => __('labels.name-fr')
        ];
    }

    private function getPrinterMessages()
    {
        return [
            'unprocessed' => __('staff/printer.unprocessed'),
            'received' => __('staff/printer.received'),
            'connecting' => __('staff/printer.connecting'),
            'reconnecting' => __('staff/printer.reconnecting'),
            'failed' => __('staff/printer.failed'),
            'connected' => __('staff/printer.connected'),
            'sent' => __('staff/printer.sent'),
            'printed' => __('staff/printer.printed'),
            'default_message' => __('staff/printer.default-message'),
        ];
    }

    private function setOrderStatus(Order $order, $productType, $status)
    {
        $order->{$productType . '_ticket_printed'} = $status;

        if ($status === Order::PENDING) {
            $order->{$productType . '_ticket_requested_at'} = Carbon::now();

            $clientIp = null;

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ips = explode(',',
                    is_array($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'][0] : $_SERVER['HTTP_X_FORWARDED_FOR']);
                $clientIp = filter_var(trim($ips[0]), FILTER_VALIDATE_IP);
            }

            $order->{$productType . '_ticket_requested_by'} = $clientIp;
        }

        $order->save();

        return true;
    }

    private function isEligible(Order $order, $type)
    {
        $eligible = true;

        $ticketStatus = $order->{$type . '_ticket_printed'};

        if (in_array($ticketStatus, Order::NOPRINTING)) {
            $eligible = false;
        }

        if ($ticketStatus === Order::PENDING) {
            $requestedAt = Carbon::parse($order->{$type . '_ticket_requested_at'});

            if (Carbon::now() < $requestedAt->addMinutes(2)) {
                $eligible = false;
            }
        }

        if ($order->pickupDetails) {
            $pickupTime = Carbon::createFromFormat('Y-m-d H:i:s',
                $order->pickupDetails->day . ' ' . $order->pickupDetails->time);

            if ($pickupTime->gt(Carbon::now('Europe/Zurich')->addMinutes(30))) {
                $eligible = false;
            }
        }

        return $eligible;
    }

    private function getDeliveryInformation(Order $order): bool|array
    {
        if (!$order->deliveryInformation) {
            return false;
        }

        return [
            'name' => strtoupper($order->deliveryInformation->first_name ? $order->deliveryInformation->first_name . ' ' . $order->deliveryInformation->last_name : $order->deliveryInformation->company_name),
            'street' => $order->deliveryInformation->street,
            'postal_code' => $order->deliveryInformation->postal_code,
            'city' => $order->deliveryInformation->city,
            'phone' => $order->deliveryInformation->phone,
            'notes' => $order->deliveryInformation->notes,
            'type' => trans('labels.type') . ': ' . trans('labels.fr-' . $order->service_method),
        ];
    }

    private function getTakeAwayInformation(Order $order): bool|array
    {
        if (!$order->pickupDetails) {
            return false;
        }

        return [
            'name' => strtoupper($order->pickupDetails->first_name ? $order->pickupDetails->first_name . ' ' . $order->pickupDetails->last_name : $order->pickupDetails->company_name),
            'company_registration' => $order->pickupDetails->company_registration,
            'email' => $order->pickupDetails->email,
            'day' => $order->pickupDetails->day,
            'time' => $order->pickupDetails->time,
            'phone' => $order->pickupDetails->phone,
            'type' => trans('labels.type') . ': ' . trans('labels.fr-' . $order->service_method),
        ];
    }
}
