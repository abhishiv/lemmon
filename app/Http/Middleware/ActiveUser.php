<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Void_;

class ActiveUser
{
    /**
     * Handle an incoming request.
     * Check if the restaurant | user are blocked and redirect accordingly
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse|Void
     */
    public function handle(Request $request, Closure $next)
    {
       /* $user = Auth::user();
        $restaurant = Restaurant::find($user->restaurant_id);

        if (!$restaurant || $user->status === User::BLOCKED || $restaurant->status === Restaurant::BLOCKED) {

            Auth::logout();

            return redirect()->route('login')->with(['error' => 'You are blocked!']);
        }

        if($user->status === User::OUTOFOFFICE) {
            Auth::logout();

            return redirect()->route('login')->with(['error' => 'Your account is marked as out of office. Contact your manager to reactivate your account.']);
        }*/

        return $next($request);
    }
}
