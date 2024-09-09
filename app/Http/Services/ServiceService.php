<?php

namespace App\Http\Services;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Support\Arr;

class ServiceService
{
    public function store($request)
    {
        $order = Service::max('order');

        $service = Service::create([
            'name' => $request->name,
            'days' => $request->days,
            'description' => $request->description,
            'order' => $order ? $order + 1 : 1,
            'hide_unavailable' => $request->hide_unavailable ? true : null,
            'visible_only_to_staff' => $request->visible_only_to_staff ? true : null,
            'restaurant_id' => auth()->user()->restaurant_id,
            'status' => Service::ACTIVE,
        ]);

        $serviceTypes = ServiceType::whereIn('alias', $request->type)->pluck('id')->toArray();

        $service->serviceTypes()->attach($serviceTypes);

        $service->products()->sync(ProductService::mapProductsOrder($request->products));

        return $service;
    }

    public function update($service, $request): void
    {
        $service->update([
            'name' => $request->name,
            'days' => $request->days,
            'description' => $request->description,
            'hide_unavailable' => $request->hide_unavailable ? true : null,
            'visible_only_to_staff' => $request->visible_only_to_staff ? true : null,
            'status' => $request->status == 'inactive' ? Service::INACTIVE : Service::ACTIVE,
        ]);

        $serviceTypes = ServiceType::whereIn('alias', $request->type)->pluck('id')->toArray();

        $service->serviceTypes()->sync($serviceTypes);

        $service->products()->sync(ProductService::mapProductsOrder($request->products));
    }

    public function destroy(Service $service): bool
    {
        if ($service->products->isNotEmpty()) {
            return false;
        }

        $service->delete();

        return true;
    }

}
