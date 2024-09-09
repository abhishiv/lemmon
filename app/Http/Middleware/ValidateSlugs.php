<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValidateSlugs
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        Restaurant::where('slug', $request->route('restaurantSlug'))->firstOrFail();

        if ($request->route()->getName() == 'customer.product.show') {
            Product::where('slug', $request->route('productSlug'))->firstOrFail();

            return $next($request);
        }

        RestaurantTable::where('hash', $request->route('tableHash'))->firstOrFail();

        return $next($request);
    }
}
