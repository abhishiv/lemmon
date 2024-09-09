<?php

namespace App\Http\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Arr;

class ProductCategoryService
{
    public function store($request): ProductCategory
    {
        return ProductCategory::create([
            'name' => $request->name,
            'status' => $request->status,
            'restaurant_id' => auth()->user()->restaurant_id,
            'order' => ProductCategory::all()->count() + 1
        ]);
    }

    public function update(ProductCategory $category, $request): ProductCategory
    {
        $category->update(Arr::except($request->validated(), 'products'));

        $category->products()->sync(ProductService::mapProductsOrder($request->products));

        return $category;
    }

    public function destroy(ProductCategory $category): bool
    {
        $products = Product::whereHas('categories', function ($query) use ($category) {
            $query->where('category_id', $category->id);
        })->get();

        if ($products->isNotEmpty()) {
            return false;
        }

        $category->delete();

        return true;
    }
}
