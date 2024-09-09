<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\CustomerRestaurantService;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Http\Services\ProductService;
use App\Models\Extra;
use App\Models\Service;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RestaurantController extends Controller
{
    public function __construct(protected ProductService $productService, protected CustomerRestaurantService $customerService)
    {
    }

    public function index($restaurantSlug, $tableHash): Factory|View|Application|RedirectResponse
    {
        $restaurant = Restaurant::where('slug', $restaurantSlug)->first();

        $table = RestaurantTable::where('hash', $tableHash)->first();

        if($table->status == RestaurantTable::UNAVAILABLE) {
            return view('customer.table-unavailable', compact('restaurant'));
        }

        if($restaurant->status == Restaurant::BLOCKED) {
            return abort(404);
        }

        if(!$restaurant->valid_working_hours) {
            return redirect()->route('customer.restaurant.unavailable', ['restaurantSlug' => $restaurantSlug, 'tableHash' => $tableHash]);
        }

        $this->customerService->index($table, $restaurant);

        $categories = collect([]);

        $tableType = $table->type;

        $services = Service::where('restaurant_id', $restaurant->id)
            ->where('status', Service::ACTIVE)
            ->whereHas('serviceTypes', function ($query) use ($tableType) {
                $query->where('alias', $tableType);
            })
            ->with('products', 'products.images', 'products.services', 'products.categories')
            ->orderBy('order')
            ->get();

        return view('customer.menu', compact('services', 'categories', 'restaurant'));
    }

    public function show($restaurantSlug, $productSlug): Factory|View|Application|Response
    {
        if(empty(session('restaurant.id')) || empty(session('table.url'))) {
            return response(view('errors.custom', [
                'text' => __('labels.no-table-in-session')
            ]));
        }

        $restaurant = Restaurant::where('slug', $restaurantSlug)->first();

        $product = Product::where('restaurant_id', $restaurant->id)->where('slug', $productSlug)->with([
            'bundles',
            'bundles.extras' => function ($extrasQuery) {
                $extrasQuery->where('status', Extra::AVAILABLE);
            },
            'bundles.extraProducts' => function ($extraProductsQuery) {
                $extraProductsQuery->where('status', Product::AVAILABLE);
            },
            'services']
        )->first();

        return view('customer.product', compact('product'));
    }

    public function onesignal(Request $request)
    {
        $this->customerService->onesignal($request);
    }

    public function unavailable($restaurantSlug, $tableHash)
    {
        $restaurant = Restaurant::where('slug', $restaurantSlug)->first();

        if($restaurant->valid_working_hours) {
            return redirect()->route('customer.menu', ['restaurantSlug' => $restaurantSlug, 'tableHash' => $tableHash]);
        }

        return view('customer.out-of-working-hours');
    }

    public function groupOrder(Request $request)
    {
        return $this->customerService->groupOrder($request);
    }
}
