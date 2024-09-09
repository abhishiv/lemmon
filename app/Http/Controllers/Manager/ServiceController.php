<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceFormRequest;
use App\Http\Services\ServiceService;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function __construct(protected ServiceService $service)
    {
    }

    public function list(): Factory|View|Application
    {
        return view('manager.services.list');
    }

    public function create(): Factory|View|Application
    {
        $categories = ProductCategory::with('products')->get();

        return view('manager.services.create', compact('categories'));
    }

    public function store(ServiceFormRequest $request): RedirectResponse
    {
        $this->service->store($request);

        return redirect()->route('manager.service.list')->with(['success' => __('labels.service-saved')]);
    }

    public function edit(Service $service): Factory|View|Application
    {
        $categs = ProductCategory::with('products', 'products.services')->get();

        $categories = [];

        foreach ($categs as $category) {
            foreach ($category['products'] as $product) {
                $categories[$category['id']]['categ']['name'] = $category['name'];
                $categories[$category['id']]['categ']['id'] = $category['id'];
                $categories[$category['id']]['products'][$product['id']] = $product;
                if (!empty($product['services'])) {
                    foreach ($product['services'] as $product_service) {
                        if ($product_service['id'] == $service->id) {
                            unset($categories[$category['id']]['products'][$product['id']]);
                        }
                    }
                }
            }
        }

        $baseProducts = $service->products()->has('categories')->get();

        $serviceCategories = [];

        $serviceProducts = $baseProducts->map(function ($product) {
            return [
                $product->categories['0']->id => [
                    'id' => $product->id,
                    'name' => $product->name,
                    // 'category_id' => $product->categories['0']->id,
                ]
            ];
        })->reduce(function ($carry, $product) {
            $carry[array_key_first($product)][] = $product[array_key_first($product)];
            return $carry;
        });

        if (!empty($serviceProducts)) {
            $serviceCategories = array_keys($serviceProducts);
        }
        return view('manager.services.edit', compact('service', 'categories', 'serviceProducts', 'serviceCategories'));
    }

    public function update(ServiceFormRequest $request, Service $service): RedirectResponse
    {
        $this->service->update($service, $request);

        return redirect()->route('manager.service.list')->with(['success' => __('labels.service-saved')]);
    }

    public function updateOrder(Request $request): bool
    {
        $order = $request->data;

        if (!is_array($order)) {
            return false;
        }

        foreach ($order as $key => $value) {
            if (!is_null($value)) {
                Service::find($key)->update(['order' => $value + 1]);
            }
        }

        $currentOrder = 0;
        $services = Service::orderBy('order', 'ASC')->get();
        foreach ($services as $service) {
            $service->update([
                'order' => ++$currentOrder
            ]);
        }
        return true;
    }

    public function copy(Service $service)
    {
        $newService = $service->replicate();
        $newService->name = $service->name . ' - ' . 'copy';
        $newService->status = Service::INACTIVE;
        $newService->save();

        $products = $service->products()->get();

        foreach ($products as $product) {
            $newService->products()->attach($product->id, ['order' => $product->pivot->order]);
        }

        return redirect()->route('manager.service.list')->with(['success' => __('manager/services.service-copied')]);
    }

    public function destroy(Service $service): RedirectResponse
    {
        return redirect()->route('manager.service.list')->with($this->service->destroy($service) ? ['success' => __('labels.service-removed')] : ['error' => __('labels.service-remove-products')]);
    }

    public function dataTable(): JsonResponse
    {
        $services = Service::select([
            'id',
            'name',
            'status',
            'order'
        ])->with('products')->orderBy('order', 'asc')->get();

        return DataTables::of($services)
            ->addColumn('action', function ($service) {
                $editRoute = route('manager.service.edit', $service->id);
                $deleteRoute = route('manager.service.destroy', $service->id);
                $copyRoute = route('manager.service.copy', $service->id);
                return \view('components.data-tables.action', compact('editRoute', 'deleteRoute', 'copyRoute'))->render();
            })
            ->editColumn('status', function ($service) {
                return "<span class='status-{$service->status}'>" . trans('labels.' . $service->status) . "</span>";
            })
            ->addColumn('productsNumber', function ($service) {
                return $service->products->count();
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
