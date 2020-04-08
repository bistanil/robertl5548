<?php

namespace App\Http\Middleware;

use Closure;
use App;
use LaravelLocalization;

class FrontLocalization
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
        App::setLocale(LaravelLocalization::getCurrentLocale());
        return $next($request);
    }
}
