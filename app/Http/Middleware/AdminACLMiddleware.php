<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Libraries\AdminAccessControl;
use Illuminate\Support\Facades\Auth;
use Exception;

class AdminACLMiddleware
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
        if (Auth::guard()->user() == null) return redirect()->guest('admin-login');       
        $ac = new AdminAccessControl($request->route());
        if (!$ac->hasPermission()) return redirect('user-unauthorized');
        return $next($request);    
    }
}
