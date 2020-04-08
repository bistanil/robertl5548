<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CarModelType;

class CheckCarMiddleware
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
        $type = new CarModelType();
        $type = $type->bySlug($request->typeSlug);
        if ($type == null && isset($request->typeSlug)) return redirect(route('front-home'));
        session()->put('type',$type);
        return $next($request);    
    }
}
