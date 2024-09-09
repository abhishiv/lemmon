<?php

namespace App\Http\Services;

use App\Models\Extra;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class CartService
{
    /**
     * Add products to cart
     * @param $request
     * @return float|int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function add($request): float|int
    {
        $cart = session()->get('cart');
        $quantity = session()->get('quantity');
        $productId = $request->product_id;
        if(isset($request['bundle'])) {
            foreach($request['bundle'] as $type => $bundle) {
                foreach($bundle as $id => $item) {
                    $productId .= '_'.$type.'_';
                    foreach($item as $item => $price) {
                        $productId .= $item;
                    }
                }
            }
        }

        if(isset($quantity[$request->product_id])) {
            $quantity = $quantity[$request->product_id];
            $quantity += $request->quantity;
            session()->put('quantity.' . $request->product_id, $quantity);
        } else {
            session()->put('quantity.' . $request->product_id, $request->quantity);
        }
        if (isset($cart[$productId])) {
            $product = $cart[$productId];
            $product['quantity'] += $request->quantity;
            $product['notes'] = $request->itemNotes ?? '';
            session()->put('cart.' . $productId, $product);
            return array_sum(array_column(session('cart'), 'quantity'));
        }


        //Put items if they don't exist
        session()->put('cart.' . $productId, [
            'id' => $request->product_id,
            'quantity' => $request->quantity,
            'notes' => $request->itemNotes ?? '',
            'bundle' => $request->bundle ?? null
        ]);
        return array_sum(array_column(session('cart'), 'quantity'));
    }

    public function dine($request)
    {
        $cart = $this->checkCart();

        //add takeaway to the current cart
        if ($cart) {
            $cart = session()->get('cart');

            $cart['takeaway'] = $request->takeaway;

            if($request->takeaway) {
                $restaurant = Restaurant::find(session('restaurant.id'));
                $cart['takeaway_discount'] = restaurant_settings_get('discount_takeaway', $restaurant);
            } else {
                $cart['takeaway_discount'] = null;
            }

            session()->put('cart', $cart);
            $res = [
                'total' => priceFormat($this->total()),
                'takeaway_discount' => $cart['takeaway_discount']
            ];
        }
        return $res ?? null;
    }

    public function getTotals($request)
    {
        $cart = $this->checkCart();

        if (!$cart) {
            return false;
        }

        $result = [
            'subtotal' => $this->calculateSubtotal($cart),
            'chez_mama_id' => env('CHEZ_MAMA_ID'),
        ];

        if ($request->customer_type === 'company' && $this->hasCompanyDiscount() && $this->isCompanyDiscountApplicable($result['subtotal'])) {
            $companyDiscount = $this->calculateCompanyDiscount($result['subtotal']);

            $result['total'] = priceFormat($result['subtotal'] - $companyDiscount['discount_net']);

            return array_merge($result, $companyDiscount);
        }

        $result['total'] = priceFormat($this->total());

        if(
            !empty($cart['takeaway']) 
            && !empty($cart['takeaway_discount']) 
            && $cart['takeaway_discount'] != 0
            && $cart['takeaway_discount'] != ''
        ) {
            $result['discount'] = true;
            $result['discount_type'] = 'takeaway';
            $result['discount_value'] = $cart['takeaway_discount'];
        } else {
            $result['discount'] = false;
        }

        return $result;
    }

    public function hasCompanyDiscount() {
        $restaurant = Restaurant::find(session('restaurant.id'));

        return $restaurant->id == env('CHEZ_MAMA_ID') && $this->getCompanyDiscount() !== false;
    }

    public function calculateCompanyDiscount($subtotal)
    {
        $discount = $this->getCompanyDiscount();

        return [
            'discount_net' => priceFormat($subtotal * $discount / 100),
            'discount' => true,
            'discount_type' => 'company',
            'discount_value' => $discount,
        ];
    }

    public function getCompanyDiscount()
    {
        $discount = 10;

        return $discount > 0 ? $discount : false;  
    }

    public function isCompanyDiscountApplicable($total)
    {
        return $total >= 60;
    }

    public function update($request)
    {
        $cart = session()->get('cart');
        $quantity = session()->get('quantity');

        $productId = explode('_', $request->product_id)[0];

        //Add quantity to existing items (from CART)
        if (isset($cart[$request->product_id])) {
            $product = $cart[$request->product_id];
            $oldQuantity = $product['quantity'];
            $product['quantity'] = $request->quantity;

            session()->put('cart.' . $request->product_id, $product);

            $res = [
                'total' => priceFormat($this->total()),
                'productTotal' => priceFormat($this->productSum($request->product_id, $request->quantity, $product['bundle'] ?? null)),
            ];

            $quantity = $quantity[$productId];
            $quantity += ($request->quantity - $oldQuantity);
            session()->put('quantity.'.$productId, $quantity);
        }

        return $res ?? null;
    }
    public function delete($request): float|int|null
    {
        $cart = session()->get('cart');
        $quantity = session()->get('quantity');

        $productId = explode('_', $request->product_id)[0];

        if (isset($cart[$request->product_id])) {
            $quantity = $quantity[$productId];
            $quantity -= $request->quantity;
            session()->put('quantity.' . $productId, $quantity);
            session()->forget('cart.' . $request->product_id);
        }


        if($quantity == 0) {
            session()->forget('quantity.' . $productId);
        }

        return $this->total();
    }

    public function products(): array|bool
    {
        $cart = $this->checkCart();

        if (!$cart) {
            return false;
        }

        $products = [];

        foreach ($cart as $productId => $item) {
            if (!isset($products[$productId])) {
                if (is_array($item) && isset($item['quantity'])) {
                    $product = Product::find($productId);
                    $product->quantity = $item['quantity'];
                    $product->notes = $item['notes'];
                    $product->bundles->products = [];
                    $product->bundles->extras = [];
                    $product->bundles->removables = [];
                    $product->bundles->all = [];
                    if(isset($item['bundle'])) {
                        foreach($item['bundle'] as $type => $bundle) {
                            foreach($bundle as $index => $items) {
                                foreach($items as $id => $price) {
                                    $product->bundles->{$type}[$index][$id] = ($type == 'products' ? Product::find($id) : Extra::find($id));
                                    $product->bundles->{$type}[$index][$id]->price = $price;
                                    $product->bundles->all[] = $product->bundles->{$type}[$index][$id];
                                }
                            }
                        }
                        $product->price = $this->productSum($productId, $item['quantity'], $item['bundle'] ?? null);
                    }
                    $products[$productId] = $product;
                }
            }
        }
        return $products;
    }


    public function total(): int|float|bool|null
    {
        $cart = $this->checkCart();

        if (!$cart) {
            return false;
        }

        $discount = 0;

        if(!empty($cart['takeaway']) && !empty($cart['takeaway_discount']) && $cart['takeaway_discount'] != 0) {
            $discount = $cart['takeaway_discount'] == '' ? 0 : $cart['takeaway_discount'];
        }

        $totalPrice = null;

        unset($cart['takeaway_discount']);
        unset($cart['takeaway']);

        foreach ($cart as $item) {
            if ($item['id']) {
                $product = Product::find($item['id']);
                if(isset($item['bundle'])) {
                    $product->bundles->products = [];
                    $product->bundles->extras = [];
                    $product->bundles->removables = [];
                    foreach($item['bundle'] as $type => $bundle) {
                        foreach($bundle as $id => $price) {
                            $product->bundles->{$type}[$id]['price'] = $price;
                        }
                    }
                }
                $totalPrice += $this->productSum($item['id'], $item['quantity'], $item['bundle'] ?? null);
            }
        }

        if($discount) {
            $totalPrice = $totalPrice - ($totalPrice * $discount / 100);
        }

        return $totalPrice;
    }

    public function calculateSubtotal($cart)
    {
        $subtotal = 0;

        unset($cart['takeaway_discount']);
        unset($cart['takeaway']);

        foreach ($cart as $item) {
            if ($item['id']) {
                $product = Product::find($item['id']);
                if(isset($item['bundle'])) {
                    $product->bundles->products = [];
                    $product->bundles->extras = [];
                    $product->bundles->removables = [];
                    foreach($item['bundle'] as $type => $bundle) {
                        foreach($bundle as $id => $price) {
                            $product->bundles->{$type}[$id]['price'] = $price;
                        }
                    }
                }
                $subtotal += $this->productSum($item['id'], $item['quantity'], $item['bundle'] ?? null);
            }
        }

        return $subtotal;
    }

    public function productSum($id, $qty, $bundle = null): float
    {
        $product = Product::findOrFail($id);

        if(isset($bundle)) {
            $product->bundles->products = [];
            $product->bundles->extras = [];
            $product->bundles->removables = [];
            foreach($bundle as $type => $bundle) {
                foreach($bundle as $index => $item) {
                    foreach($item as $id => $price) {
                        $product->bundles->{$type}[$id]['price'] = $price;
                    }
                }
            }
            $product->price = array_sum(array_column($product->bundles->products, 'price')) + array_sum(array_column($product->bundles->extras, 'price')) + $product->sale_price;
        }

        return $product->price * $qty;
    }

    public function checkCart(): array|bool
    {
        $cart = session()->get('cart');

        if (!isset($cart)) {
            abort(404);
        }
        if (empty($cart)) {
            return false;
        }

        return $cart;
    }

    public function notes()
    {
        $notes = session('notes');

        if (isset($notes)) {
            return session('notes');
        }

        return false;
    }

    public function getTipsRecommendedAmounts() {
        $restaurant = Restaurant::find(session('restaurant.id'));

        return array_filter([
            restaurant_settings_get('tip_recommended_amount_1', $restaurant),
            restaurant_settings_get('tip_recommended_amount_2', $restaurant),
            restaurant_settings_get('tip_recommended_amount_3', $restaurant)
        ], function ($amount) {
            return $amount != null;
        });
    }
}
