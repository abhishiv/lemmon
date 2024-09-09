<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantFormRequest;
use App\Http\Requests\LogoFormRequest;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use App\Http\Services\RestaurantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class RestaurantController extends Controller
{
    protected RestaurantService $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    public function list(): Factory|View|Application
    {
        if (Restaurant::all()->isEmpty()){
            return view('admin.dashboard.index');
        }

        return view('admin.restaurants.list');
    }

    public function create(): Factory|View|Application
    {
        return view('admin.restaurants.create');
    }

    public function store(RestaurantFormRequest $request): RedirectResponse
    {
        $request->request->add(['role' => User::MANAGER]);

        $restaurant = $this->restaurantService->store($request);

        return redirect()->route('admin.restaurant.list')->with(['success' => 'The restaurant has been saved']);
    }

    public function show(Restaurant $restaurant): Factory|View|Application
    {
        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function edit(Restaurant $restaurant): Factory|View|Application
    {
        return view('admin.restaurants.edit', compact('restaurant'));
    }

    public function update(RestaurantFormRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $restaurant = $this->restaurantService->update($restaurant, $request);

        if ($request->submit == 'save') {
            return redirect()->route('admin.restaurant.list')->with(['success' => 'The restaurant has been updated']);
        }

        return redirect()->route('admin.restaurant.list')->with(['success' => 'Invitation has been sent!']);
    }

    public function updateStatus(Request $request, Restaurant $restaurant)
    {
        $restaurant->update(['status' => $request->status]);

        return redirect()->route('admin.restaurant.show', $restaurant->id);
    }

    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        $this->restaurantService->destroy($restaurant);

        return redirect()->route('admin.restaurant.list')->with(['success' => 'The restaurant has been removed']);
    }

    public function dataTable(): JsonResponse
    {
        $restaurants = Restaurant::select([
            'id',
            'name',
            'onboarded_at',
            'onboarded_by',
            'status'
        ])->with('onboarded')->get();

        return DataTables::of($restaurants)
            ->addColumn('action', function ($restaurant) {
                $editRoute = route('admin.restaurant.edit', $restaurant->id);
                $deleteRoute = route('admin.restaurant.destroy', $restaurant->id);

                return \view('components.data-tables.action', compact('editRoute', 'deleteRoute'))->render();
            })
            ->editColumn('onboarded_by', function ($restaurant) {
                return $restaurant->onboarded->name;
            })
            ->editColumn('status', function ($restaurant) {
               return "<span class='status-{$restaurant->status}'>".trans('labels.' .$restaurant->status)."</span>";
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function getLogo(Restaurant $restaurant): bool|string
    {
        return json_encode($restaurant->getLogo());
    }

    public function verifyLogo(LogoFormRequest $request): JsonResponse
    {
        return response()->json(['success' => 'ok'], 200);
    }
}
