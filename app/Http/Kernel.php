<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\LanguageMiddleware::class,
            \App\Http\Middleware\RedirectToLowercase::class,
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'clientAuth' => \App\Http\Middleware\ClientAuthenticate::class,
        'client' => \App\Http\Middleware\ClientRedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'localize' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
        'localizationRedirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        'localeSessionRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
        'frontLocalization' => \App\Http\Middleware\FrontLocalization::class,
        'checkCar' => \App\Http\Middleware\CheckCarMiddleware::class,
        'adminACL' => \App\Http\Middleware\AdminACLMiddleware::class,
        'checkStatus' => \App\Http\Middleware\CheckClientStatus::class,
        'checkCompanyAccess' => \App\Http\Middleware\CheckCompanyAccess::class,
        'checkOrderAccess' => \App\Http\Middleware\CheckOrderAccess::class,
        'checkDeliveryAddressAccess' => \App\Http\Middleware\CheckDeliveryAddressAccess::class,
        'checkClientCarAccess' => \App\Http\Middleware\CheckClientCarAccess::class,
        'checkProductReviewAccess' => \App\Http\Middleware\CheckProductReviewAccess::class,
        'checkInactiveProductAccess' => \App\Http\Middleware\CheckInactiveProductAccess::class,        
    ];
}
