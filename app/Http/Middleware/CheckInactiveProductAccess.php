<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\CatalogProduct;

class CheckInactiveProductAccess
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
       $product = CatalogProduct::whereSlug($request->slug)->get()->first();
       if ($product->active == 'active') return $next($request);
       if ($product->active == 'inactive') return redirect(route('front-home'));
        return redirect(route('front-home'));
    }
}