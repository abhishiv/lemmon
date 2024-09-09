<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodTypeRequest;
use App\Http\Requests\UpdateFoodTypeRequest;
use App\Http\Services\FoodTypeService;
use App\Models\FoodType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodTypeController extends Controller
{
    public function __construct(protected FoodTypeService $foodTypeService) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('manager.courses.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('manager.courses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFoodTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFoodTypeRequest $request)
    {
        $this->foodTypeService->store($request);

        return view('manager.courses.list')
            ->with('success', __('course-added'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FoodType  $foodType
     * @return \Illuminate\Http\Response
     */
    // public function show(FoodType $foodType)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FoodType  $foodType
     * @return \Illuminate\Http\Response
     */
    public function edit(FoodType $foodType)
    {
        if($foodType->restaurant_id != auth()->user()->restaurant_id) {
            abort(404);
        }

        return view('manager.courses.edit', compact("foodType"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFoodTypeRequest  $request
     * @param  \App\Models\FoodType  $foodType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFoodTypeRequest $request, FoodType $foodType)
    {
        $this->foodTypeService->update($foodType, $request);

        return view('manager.courses.list')
        ->with('success', __('course-edited'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FoodType  $foodType
     * @return \Illuminate\Http\Response
     */
    public function destroy(FoodType $foodType)
    {
        // Check if this is the last food type for this restaurant
        if($foodType->restaurant->foodTypes()->count() == 1) {
            return redirect()->route('manager.course.index')
            ->with('error', __('labels.course-last'));
        }

        // Check if there are any products assigned to this food type
        if($foodType->products()->count()) {
            return redirect()->route('manager.course.index')
            ->with('error', __('labels.course-has-products'));
        }

        $restaurant = $foodType->restaurant;

        $foodType->delete();

        $restaurant->reorderFoodTypes();

        return redirect()->route('manager.course.index')
        ->with('success', __('labels.course-deleted'));
    }

    public function dataTable(): JsonResponse
    {
        return $this->foodTypeService->dataTable();
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->foodTypeService->reorder($request);

        return new JsonResponse(['success' => true]);
    }
}
