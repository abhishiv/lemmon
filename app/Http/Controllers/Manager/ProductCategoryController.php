<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryFormRequest;
use App\Http\Services\ProductCategoryService;
use App\Models\ProductCategory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoryController extends Controller
{
    public function __construct(protected ProductCategoryService $productCategoryService)
    {
    }

    public function list(): Factory|View|Application
    {
        $categories = ProductCategory::all();

        return view('manager.product-categories.list', compact('categories'));
    }

    public function create(): Factory|View|Application
    {
        return view('manager.product-categories.create');
    }

    public function store(ProductCategoryFormRequest $request): RedirectResponse
    {
        $category = $this->productCategoryService->store($request);

        return redirect()->route('manager.product.category.list')->with('success', 'The category has been created!');
    }

    public function edit(ProductCategory $productCategory): Factory|View|Application
    {
        return view('manager.product-categories.edit', ['category' => $productCategory]);
    }

    public function update(ProductCategoryFormRequest $request, ProductCategory $productCategory): RedirectResponse
    {
        $this->productCategoryService->update($productCategory, $request);

        return redirect()->route('manager.product.category.list')->with('success', 'The category has been updated!');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        return redirect()->route('manager.product.category.list')->with($this->productCategoryService->destroy($productCategory) ? ['success' => __('labels.delete-category')] : ['error' => __('labels.category-remove-products')]);
    }

    public function updateOrder(Request $request): bool
    {
        $order = $request->data;

        if (!is_array($order)){
            return false;
        }
        foreach ($order as $key => $value){
            if (!is_null($value)){
                ProductCategory::find($key)->update(['order' => $value + 1]);
            }
        }

        $currentOrder = 0;
        $categories = ProductCategory::orderBy('order', 'ASC')->get();
        foreach($categories as $category) {
            $category->update([
                'order' => ++$currentOrder
            ]);
        }
        return true;
    }

    public function dataTable(): JsonResponse
    {
        $categories = ProductCategory::select(['id', 'name', 'status', 'order'])->with('products')->orderBy('order', 'asc')->get();

        return DataTables::of($categories)
            ->addColumn('productNumber', function ($category){
                return $category->products->count();
            })

            ->editColumn('status', function ($category){
                return "<span class='status-{$category->status}'>".trans('labels.' .$category->status)."</span>";
            })

            ->addColumn('action', function ($category){
                $editRoute = route('manager.product.category.edit', $category->id);
                $deleteRoute = route('manager.product.category.destroy', $category->id);

                return \view('components.data-tables.action', compact('editRoute', 'deleteRoute'))->render();
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
