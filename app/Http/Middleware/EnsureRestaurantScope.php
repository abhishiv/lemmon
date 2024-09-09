<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureRestaurantScope
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return RedirectResponse|Response|mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        //Check if target item (ex: products, product-category, staff) exists in route
        if (!isset($request->route()->parameterNames()[0])) {
            return $next($request);
        }
        //If target item exists get it with route model binding
        $parameter = $request->route()->parameterNames()[0];

        $restaurant_id = $request->route()->parameters()[$parameter]->restaurant_id ?? $request->route()->parameters()[$parameter]->id;

        //Check if Authenticated user restaurant_id === Requested item restaurant_id
        if (auth()->user()->restaurant_id != $restaurant_id) {
            abort(403);
        }

        return $next($request);
    }
}
