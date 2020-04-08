<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckClientStatus
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
        if(Auth::guard('client')->check() AND Auth::guard('client')->user()->active != 'active') {
            Auth::guard('client')->logout();
            frontFlash()->error(trans('front/clients.userStatus'), trans('front/clients.userSuspended'));
            return redirect(route('client-login'));
        }
        return $response;
    }
}