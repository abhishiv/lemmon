<?php

namespace App\Http\Services;

use App\Models\FoodType;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class FoodTypeService {
    public function store($request) {
        $restaurant = Restaurant::with('foodTypes')->find(auth()->user()->restaurant_id);

        $foodType = new FoodType;

        $foodType->name = $request->input('name');
        $foodType->restaurant_id = $restaurant->id;
        $foodType->order = $restaurant->foodTypes->count();
        $foodType->save();
    }

    public function update($foodType, $request) {
        $foodType->update($request->validated());
    }

    public function dataTable() : JsonResponse {
        $foodTypes = FoodType::orderBy('order', 'ASC')->get();

        return DataTables::of($foodTypes)
            ->editColumn('order', function($foodType) {
                return $foodType->order + 1;
            })
            ->addColumn('name', function ($foodType) {
                return $foodType->name;
            })
            ->editColumn('created_at', function ($foodType){
                return Carbon::make($foodType->created_at)->format('d/m/y H:i');
            })
            ->addColumn('action', function ($foodType){
                $editRoute = route('manager.course.edit', $foodType->id);
                $deleteRoute = route('manager.course.destroy', ['foodType' => $foodType->id]);

                return \view('components.data-tables.action', compact('editRoute', 'deleteRoute'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function reorder($request) {
        try {
            $data = $request->input('data');

            if(!is_array($data)) {
                return;
            }

            $foodTypes = array_map(function($value, $key) {
                return FoodType::where('order', $key)->first();
            }, $data, array_keys($data));


            foreach($foodTypes as $foodType) {
                if(!isset($data[$foodType->order])) {
                    continue;
                }

                $foodType->order = $data[$foodType->order];
                $foodType->save();
            }

            Restaurant::find(auth()->user()->restaurant_id)->reorderFoodTypes();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
