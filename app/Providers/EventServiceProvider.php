<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\PageDelete' => [
            'App\Listeners\PageListener@delete',
        ],
        'App\Events\CatalogDelete' => [
            'App\Listeners\CatalogListener@delete',
        ],
        'App\Events\CatalogUpdate' => [
            'App\Listeners\CatalogListener@update',
        ],
        'App\Events\CatalogCategoryDelete' => [
            'App\Listeners\CatalogCategoryListener@delete',
        ],
        'App\Events\CatalogCategoryUpdate' => [
            'App\Listeners\CatalogCategoryListener@update',
        ],
        'App\Events\CatalogListDelete' => [
            'App\Listeners\CatalogListListener@delete',
        ],
        'App\Events\ManufacturerDelete' => [
            'App\Listeners\ManufacturerListener@delete',
        ],
        'App\Events\CurrencyDelete' => [
            'App\Listeners\CurrencyListener@delete',
        ],
        'App\Events\ProductDelete' => [
            'App\Listeners\ProductListener@delete',
        ],
        'App\Events\CarDelete' => [
            'App\Listeners\CarListener@delete',
        ],
        'App\Events\CarModelsGroupDelete' => [
            'App\Listeners\CarModelsGroupListener@delete',
        ],
        'App\Events\CarModelDelete' => [
            'App\Listeners\CarModelListener@delete',
        ],
        'App\Events\CarModelTypeDelete' => [
            'App\Listeners\CarModelTypeListener@delete',
        ],
        'App\Events\PartsCategoryDelete' => [
            'App\Listeners\PartsCategoryListener@delete',
        ],
        'App\Events\PartDelete' => [
            'App\Listeners\PartListener@delete',
        ],
        'App\Events\ClientDelete' => [
            'App\Listeners\ClientListener@delete',
        ],
        'App\Events\OrderDelete' => [
            'App\Listeners\OrderListener@delete',
        ],
        'App\Events\PackageDelete' => [
            'App\Listeners\PackageListener@delete',
        ],
        'App\Events\NewsCategoryDelete' => [
            'App\Listeners\NewsCategoryListener@delete',
        ],
        'App\Events\NewsPostDelete' => [
            'App\Listeners\NewsPostListener@delete',
        ],
        'App\Events\CatalogAttributeDelete' => [
            'App\Listeners\CatalogAttributeListener@delete',
        ],
        'App\Events\TransportTypeDelete' => [
            'App\Listeners\TransportTypeListener@delete',
        ],
        'App\Events\CountyDelete' => [
            'App\Listeners\CountyListener@delete',
        ],
        'App\Events\UserProfileDelete' => [
            'App\Listeners\UserProfileListener@delete',
        ],
        'App\Events\OfferDelete' => [
            'App\Listeners\OfferListener@delete',
        ],
        'App\Events\SupplierDelete' => [
            'App\Listeners\SupplierListener@delete',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}