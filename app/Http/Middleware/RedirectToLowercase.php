<?php
namespace App\Http\Middleware;
use Closure;
use \Illuminate\Support\Facades\Redirect;

class RedirectToLowercase
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! ctype_lower(preg_replace('/[^A-Za-z]/', '', $request->path())) && $request->path() !== "/") {
            $new_route = str_replace($request->path(), strtolower($request->path()), $request->fullUrl());
            return Redirect::to($new_route, 301);
        }
        return $next($request);
    }
}