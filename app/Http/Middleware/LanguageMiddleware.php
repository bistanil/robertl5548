<?php

namespace App\Http\Middleware;

use Closure;
use App;

class LanguageMiddleware
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
        if (session('language') != '') App::setLocale(session('language'));
        return $next($request);
    }
}