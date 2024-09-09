<?php

namespace App\Http\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Bundle;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Http\Services\SettingService;

class StaffCartService
{
    public function __construct(Request $request = null)
    {
        if ($request?->table_id) {
            $this->empty();
            $this->setTable($request->table_id);
        }
    }

    public function getCart()
    {
        return session()->get('staff_cart');
    }

    public function getCartOptions()
    {
        return session()->get('staff_cart_options');
    }


    public function add(Request $request): bool
    {
        $cart = $this->getCart();

        $item = $request->item;

        $product = Product::find($item['id']);

        $key = isset($item['bundle']) ? $this->buildKeyForCompositeItem($item) : $item['id'];

        if ($product->is_custom) {
            $key = $item['id'] . '_' . str_replace([',', '.'], '', $item['price']);
        }

        if (isset($cart[$key])) {
            $cartItem = $cart[$key];
            $cartItem['quantity'] += (int)$item['quantity'];

            session()->put('staff_cart.' . $key, $cartItem);
        } else {

            if (!$product->is_custom) {
                $item['price'] = $product->sale_price;
            }

            if (isset($item['bundle'])) {
                $bundleItems = [];

                foreach ($item['bundle'] as $bundle) {
                    foreach ($bundle as $bundleItem) {
                        if ($bundleItem['entity_type'] == 'products') {
                            $entity = Bundle::find($bundleItem['bundle_id'])
                                ->extraProducts()
                                ->wherePivot('entity_id', $bundleItem['entity_id'])
                                ->first();

                            $bundleItem['price'] = $entity->pivot->price;
                            $bundleItems[] = $bundleItem;
                        } elseif ($bundleItem['entity_type'] == 'extras') {
                            $entity = Bundle::find($bundleItem['bundle_id'])
                                ->extras()
                                ->wherePivot('entity_id', $bundleItem['entity_id'])
                                ->first();

                            $bundleItem['price'] = $entity->pivot->price;
                            $bundleItems[] = $bundleItem;
                        }
                    }
                }

                $item['bundle'] = $bundleItems;
                $item['bundle_total'] = $this->calculateCompositeItemPrice($item);
            }

            session()->put('staff_cart.' . $key, $item);
        }
        session()->save();

        return true;
    }

    public function update(Request $request): void
    {
        $cart = $this->getCart();

        $item = $request->item;

        if (isset($item['key'])) {
            if ($item['quantity'] < 1) {
                session()->forget('staff_cart.' . $item['key']);
            } else {
                $cartItem = $cart[$item['key']];
                $cartItem['quantity'] = $item['quantity'];

                session()->put('staff_cart.' . $item['key'], $cartItem);
            }

            session()->save();
        }
    }

    public function empty(): bool
    {
        session()->forget('staff_cart');
        session()->save();

        return $this->clearOptions();
    }

    public function clearOptions(): bool
    {
        session()->forget('staff_cart_options');
        session()->save();

        return true;
    }

    public function addNotes($notes): void
    {
        $cart = $this->getCart();

        foreach ($notes as $key => $note) {
            if (isset($cart[$key])) {
                $cartItem = $cart[$key];
                $cartItem['notes'] = $note;

                session()->put('staff_cart.' . $key, $cartItem);
            }
        }

        session()->save();
    }

    private function buildKeyForCompositeItem($item)
    {
        $result = $item['id'];

        foreach ($item['bundle'] as $key => $bundleItems) {
            $result .= '_' . $key . '_' . implode('_', array_map(function ($bundleItem) {
                    return $bundleItem['entity_id'];
                }, $bundleItems));
        }

        return $result;
    }

    private function calculateCompositeItemPrice($item): float
    {
        $bundleTotal = (float)$item['price'];

        $bundleTotal += array_reduce($item['bundle'], function ($total, $bundleItem) {
            $total += (float)$bundleItem['price'];
            return $total;
        });

        return $bundleTotal;
    }

    public function setTable($tableId)
    {
        session()->put('staff_cart_options.table', $tableId);
        session()->save();

        return true;
    }

    public function setTips($amount)
    {
        if ($amount == 0) {
            session()->forget('staff_cart_options.tips');
        } else {
            session()->put('staff_cart_options.tips', $amount);
        }

        session()->save();

        return true;
    }

    public function setDiscount($discount): bool
    {
        if ($discount['amount'] == 0) {
            session()->forget('staff_cart_options.discount');
        } else {
            session()->put('staff_cart_options.discount.type', $discount['type']);
            session()->put('staff_cart_options.discount.amount', $discount['amount']);
        }

        session()->save();

        return true;
    }

    public function getTable()
    {
        $cartOptions = $this->getCartOptions();

        if ($cartOptions && isset($cartOptions['table'])) {
            return RestaurantTable::where('id', $cartOptions['table'])->first();
        } else {
            return null;
        }
    }

    public function getProducts(): bool|array
    {
        $cart = $this->getCart();

        if (!$cart) {
            return false;
        }

        $products = [];

        foreach ($cart as $key => $item) {
            if (!isset($products[$key])) {
                if (is_array($item) && isset($item['quantity'])) {
                    $product = Product::find($item['id']);
                    $product->quantity = $item['quantity'];
                    $product->price = $item['price'];

                    if (isset($item['notes'])) {
                        $product->notes = $item['notes'] ?? null;
                    }

                    if (isset($item['bundle'])) {
                        $product->basePrice = $item['price'];
                        $bundleItems = [];

                        foreach ($item['bundle'] as $bundleItem) {
                            if ($bundleItem['entity_type'] == 'products') {
                                $entity = Bundle::find($bundleItem['bundle_id'])
                                    ->extraProducts()
                                    ->wherePivot('entity_id', $bundleItem['entity_id'])
                                    ->first();
                                $entity->price = $bundleItem['price'];

                                $bundleItems[] = $entity;
                            } elseif ($bundleItem['entity_type'] == 'extras') {
                                $entity = Bundle::find($bundleItem['bundle_id'])
                                    ->extras()
                                    ->wherePivot('entity_id', $bundleItem['entity_id'])
                                    ->first();
                                $entity->price = $bundleItem['price'];

                                $bundleItems[] = $entity;
                            }
                        }

                        $product->bundleItems = $bundleItems;
                        $product->price = $item['bundle_total'];
                    }

                    $product->total = $product->price * $product->quantity;
                    $products[$key] = $product;
                }
            }
        }

        return $products;
    }

    public function getQuantitiesByProductId(): array
    {
        $cart = $this->getCart();

        $quantities = [];

        if ($cart) {
            foreach ($cart as $item) {
                if (isset($quantities[$item['id']])) {
                    $quantities[$item['id']] += (int)$item['quantity'];
                } else {
                    $quantities[$item['id']] = (int)$item['quantity'];
                }
            }
        }

        return $quantities;
    }

    public function getTotals(): array
    {
        $cart = $this->getCart();
        $cartOptions = $this->getCartOptions();

        $settings = (new SettingService)->getSettings();

        $vat =  0;//$this->isTakeAway() || $this->isDelivery() ? (float)$settings['takeaway_vat'] : (float)$settings['vat'];

        $totals = [
            'currency' => __('labels.currency'),
            'subtotal' => priceFormat(0),
            'gross' => priceFormat(0),
            'amount_due' => priceFormat(0),
            'vat' => $vat,
            'net_vat' => priceFormat(0),
            'discount' => 0,
            'discount_net' => priceFormat(0),
        ];

        if (!$cart) {
            return $totals;
        }

        foreach ($cart as $item) {
            $totals['subtotal'] += (isset($item['bundle_total']) ? $item['bundle_total'] : $item['price']) * (int)$item['quantity'];
        }

        $totals['subtotal'] = priceFormat($totals['subtotal']);
        $totals['gross'] = $totals['subtotal'];

        // $totals['discount'] = $this->isTakeAway() ? ((float) restaurant_settings_get('discount_takeaway', auth()->user()->restaurant_id) ?? 0) : 0;
        // $totals['discount_net'] = $totals['subtotal'] * $totals['discount'] / 100;

        if (isset($cartOptions['discount'])) {
            $totals['manual_discount'] = $this->calculateManualDiscount($cartOptions['discount'], $totals['subtotal']);
            $totals['gross'] = priceFormat($totals['subtotal'] - $totals['manual_discount']['net_amount']);
        }

        // $totals['gross'] = priceFormat($totals['subtotal'] - $totals['discount_net']);


        $totals['net_vat'] = $vat > 0 ? round($totals['gross'] * ($vat / (100 + $vat)), 2) : 0;
        $totals['net_vat'] = priceFormat($totals['net_vat']);

        $totals['amount_due'] = $totals['gross'];

        if (isset($cartOptions['tips'])) {
            $totals['tips']['value'] = priceFormat($cartOptions['tips']);
            $totals['tips']['suffix'] = strtolower(__('labels.tips'));
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

    private function isTakeAway(): bool
    {
        $cartOptions = $this->getCartOptions();

        return isset($cartOptions['service_method']) && $cartOptions['service_method'] === Order::TAKEAWAY;
    }

    private function isDelivery(): bool
    {
        $cartOptions = $this->getCartOptions();

        return isset($cartOptions['service_method']) && $cartOptions['service_method'] === Order::DELIVERY;
    }
}
