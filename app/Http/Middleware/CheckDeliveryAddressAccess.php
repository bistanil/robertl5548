<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\DeliveryAddress;

class CheckDeliveryAddressAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (!isset($request->id)) return $response;
        if (DeliveryAddress::whereClient_id(Auth::guard('client')->user()->id)->whereId($request->id)->get()->count() > 0) return $response;        
        return redirect(route('client-account'));
    }
}