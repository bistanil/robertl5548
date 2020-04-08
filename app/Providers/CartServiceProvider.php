<?php 
namespace App\Providers;

 use App\Services\Cart\CartExtension;
 use Illuminate\Support\ServiceProvider;
 use Illuminate\Support\Facades\App; 

class CartServiceProvider extends ServiceProvider {

/**
 * Bootstrap the application services.
 *
 * @return void
 */
public function boot()
{
    //
}

/**
 * Register the application services.
 *
 * @return void
 */
public function register()
{
	App::bind('cart', function($app)
    {
        $session = $app['session'];
        $events = $app['events'];
        return new CartExtension($session, $events);
    });	
}

}