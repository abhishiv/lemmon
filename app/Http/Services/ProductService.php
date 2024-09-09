<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Bundle;
use App\Models\Product;
use App\Traits\ImageTrait;
use App\Models\ProductCategory;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductService
{
    use ImageTrait;

    /**
     * @param $request
     * @return Product
     */
    public function store($request): Product
    {
        $data = $request->validated();

        $product = Product::create([
            'name' => $request->name,
            'is_custom' => $data['is_custom'] ?? 0,
            'price' => $request->price,
            'special_price' => $request->special_price,
            'type' => $request->type,
            'weight' => $request->weight,
            'additional_info' => $request->additional_info,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => $request->status,
            'restaurant_id' => auth()->user()->restaurant_id,
            'food_type_id' => $request->type == 'restaurant' ? $request->food_type_id : null,
            'extra_limit' => $request->extra_limit ?? null,
            'product_limit' => $request->product_limit ?? null,
        ]);

        if (isset($data['product'])) {
            foreach ($data['product'] as $extraProduct) {
                $bundle = Bundle::create([
                    'name' => $extraProduct['groupname'],
                    'limit' => $extraProduct['grouplimit'] ?? null,
                    'min_limit' => $extraProduct['minlimit'] ?? 0,
                    'product_id' => $product->id,
                ]);

                foreach ($extraProduct['products'] as $extraItem) {
                    $bundle->extraProducts()->attach($extraItem['id'], [
                        'price' => $extraItem['price'],
                        'order' => $extraItem['order'],
                        'entity_type' => 'product',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        if (isset($data['extra'])) {
            foreach ($data['extra'] as $extraExtraItem) {
                $extraBundle = Bundle::create([
                    'name' => $extraExtraItem['groupname'],
                    'limit' => $extraExtraItem['grouplimit'] ?? null,
                    'min_limit' => $extraExtraItem['minlimit'] ?? 0,
                    'product_id' => $product->id,
                ]);

                foreach ($extraExtraItem['extras'] as $extraItem) {
                    $extraBundle->extras()->attach($extraItem['id'], [
                        'price' => $extraItem['price'],
                        'order' => $extraItem['order'],
                        'entity_type' => 'extra',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
        if (isset($data['removable'])) {
            foreach ($data['removable'] as $removableExtraItem) {
                $removableBundle = Bundle::create([
                    'name' => $removableExtraItem['groupname'],
                    'limit' => $removableExtraItem['grouplimit'],
                    'product_id' => $product->id,
                ]);

                foreach ($removableExtraItem['removables'] as $removableItem) {
                    $removableBundle->removables()->attach($removableItem['id'], [
                        'order' => $removableItem['order'],
                        'entity_type' => 'removable',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        $order = ProductCategory::with('products')->find($request->category_id)->products()->orderBy('pivot_order',
            'desc')->first();

        $product->categories()->attach([$request->category_id => ['order' => isset($order->pivot->order) ? $order->pivot->order + 1 : 1]]);

        $product->related()->attach($request->related_id);

        $product->extras()->attach($request->extras_id);

        $this->upload($product, $request['images']);

        return $product;
    }

    /**
     * @param Product $product
     * @param $request
     * @return Product
     */
    public function update(Product $product, $request): Product
    {
        $validated = $request->validated();
        // Check if product type changed to bar
        if (isset($validated['type']) && $validated['type'] == Product::BAR) {
            $validated['food_type_id'] = null;
        }

        if (!isset($validated['is_custom'])) {
            $validated['is_custom'] = 0;
        }

        $product->update($validated);

        $product->bundles()->delete();

        if (isset($validated['product'])) {
            foreach ($validated['product'] as $extraProduct) {
                $extraProductBundle = $product->bundles()->create([
                    'name' => $extraProduct['groupname'],
                    'limit' => $extraProduct['grouplimit'] ?? null,
                    'min_limit' => $extraProduct['minlimit'] ?? 0,
                    'product_id' => $product->id,
                ]);

                $extraProductBundle->extraProducts()->delete();
                foreach ($extraProduct['products'] as $extraItem) {
                    $extraProductBundle->extraProducts()->attach($extraItem['id'], [
                        'price' => $extraItem['price'],
                        'order' => $extraItem['order'],
                        'entity_type' => 'product',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
        if (isset($validated['extra'])) {
            foreach ($validated['extra'] as $extraProduct) {
                $extraBundle = $product->bundles()->create([
                    'name' => $extraProduct['groupname'],
                    'limit' => $extraProduct['grouplimit'] ?? null,
                    'min_limit' => $extraProduct['minlimit'] ?? 0,
                    'product_id' => $product->id,
                ]);

                $extraBundle->extras()->delete();
                foreach ($extraProduct['extras'] as $extraItem) {
                    $extraBundle->extras()->attach($extraItem['id'], [
                        'price' => $extraItem['price'],
                        'order' => $extraItem['order'],
                        'entity_type' => 'extra',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
        if (isset($validated['removable'])) {
            foreach ($validated['removable'] as $removableProduct) {
                $removableBundle = $product->bundles()->create([
                    'name' => $removableProduct['groupname'],
                    'limit' => $removableProduct['grouplimit'],
                    'product_id' => $product->id,
                ]);

                $removableBundle->removables()->delete();
                foreach ($removableProduct['removables'] as $removableItem) {
                    $removableBundle->removables()->attach($removableItem['id'], [
                        'order' => $removableItem['order'],
                        'entity_type' => 'removable',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        $order = ProductCategory::with('products')->find($request->category_id)->products()->orderBy('pivot_order',
            'desc')->first();

        $product->categories()->sync([$request->category_id => ['order' => isset($order->pivot->order) ? $order->pivot->order : 1]]);

        $product->related()->sync($request->related_id);

        $this->upload($product, $request['images']);

        return $product;
    }

    /**
     * Return all products available/out of stock grouped in categories
     * @return array
     */
    public function getProductsGroupedByCategory(): array
    {
        $records = Product::whereIn('status', [Product::AVAILABLE, Product::OUTOFSTOCK])->get();

        $products = [];

        if (!empty($records)) {
            foreach ($records as $product) {
                foreach ($product->categories as $cat) {
                    $products[$cat->id][$product->id] = $product;
                }
            }
        }

        return $products;
    }

    public function copy(Product $product): Product
    {
        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' - ' . 'copy';
        $newProduct->slug = $product->slug . ' - ' . '2';
        $newProduct->status = Product::UNAVAILABLE;
        $newProduct->save();

        $categories = $product->categories()->get();

        foreach ($categories as $category) {
            $newProduct->categories()->attach($category->id, ['order' => $category->pivot->order]);
        }

        $images = $product->images()->get();

        foreach ($images as $image) {
            $this->duplicate($image, $newProduct);
        }

        return $newProduct;
    }

    /**
     * Checks if the product is present in an active order before deleting
     * Soft deletes product and deletes product image
     * @param Product $product
     * @return bool
     */
    public function destroy(Product $product): bool
    {
        $activeOrders = OrderService::activeOrders();

        if (!empty($activeOrders)) {
            foreach ($activeOrders as $order) {
                foreach ($order->items as $item) {
                    if ($product->id == $item->products->id) {
                        return false;
                    }
                }
            }
        }

        $this->delete($product);

        $product->delete();

        return true;
    }

    public static function mapProductsOrder($products): array
    {
        $order = [];

        if (empty($products) || !is_iterable($products)) {
            return $order;
        }

        foreach ($products as $key => $value) {
            $order[$value]['order'] = $key + 1;
        }

        return $order;
    }

    private function validateFoodTypeChange($product, $request)
    {
        if (Order::with('items.products')
            ->whereRelation('items.products', 'id', $product->id)
            ->whereIn('status', Order::STAFFSTATUS)
            ->count()) {
            $this->throwValidationError('food_type_id', __('labels.product-course-active-orders'));
        }

        $product->food_type_id = $request['food_type_id'];
        $product->save();

        // Change only the closed orders, initial ones do not have any generated food statuses
        $closedOrders = Order::with('items.products')
            ->whereRelation('items.products', 'id', $product->id)
            ->where('status', Order::CLOSED)
            ->get();

        foreach ($closedOrders as $order) {
            $order->reloadStatuses(true);
        }

        return true;
    }

    private function throwValidationError($key, $message)
    {
        $validator = Validator::make([], []);

        $validator->errors()->add($key, $message);

        $errors = $validator->errors();
        throw new HttpResponseException(
            redirect()->back()->withErrors($errors)
        );
    }
}
