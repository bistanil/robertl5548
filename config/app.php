<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    'name' => env('APP_NAME', 'GarageAuto'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Europe/Bucharest',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'ro',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'ro',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => env('APP_LOG', 'single'),
    'url' => env('APP_URL'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,
        App\Providers\ViewComposerServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        //HW
        Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider::class,
        Rutorika\Sortable\SortableServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,
        GeneaLabs\LaravelCaffeine\Providers\LaravelCaffeineService::class,
        Laracasts\Utilities\JavaScript\JavaScriptServiceProvider::class,
        Barryvdh\TranslationManager\ManagerServiceProvider::class,
        Msurguy\Honeypot\HoneypotServiceProvider::class,
        Barryvdh\Snappy\ServiceProvider::class,

        App\Providers\CartServiceProvider::class, 
        App\Providers\ProductInfoServiceProvider::class,
        App\Providers\ProductServiceProvider::class,
        App\Providers\ProductAttributeServiceProvider::class,
        App\Providers\ProductPriceServiceProvider::class,
        App\Providers\ProductCategoryServiceProvider::class,
        App\Providers\ProductDimensionServiceProvider::class,
        App\Providers\PartServiceProvider::class,
        App\Providers\PartCategoryServiceProvider::class,
        App\Providers\PartCodesServiceProvider::class,

        App\Providers\MobilPayServiceProvider::class,
        App\Providers\SchemaOrgServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App'       => Illuminate\Support\Facades\App::class,
        'Artisan'   => Illuminate\Support\Facades\Artisan::class,
        'Auth'      => Illuminate\Support\Facades\Auth::class,
        'Blade'     => Illuminate\Support\Facades\Blade::class,
        'Cache'     => Illuminate\Support\Facades\Cache::class,
        'Config'    => Illuminate\Support\Facades\Config::class,
        'Cookie'    => Illuminate\Support\Facades\Cookie::class,
        'Crypt'     => Illuminate\Support\Facades\Crypt::class,
        'DB'        => Illuminate\Support\Facades\DB::class,
        'Eloquent'  => Illuminate\Database\Eloquent\Model::class,
        'Event'     => Illuminate\Support\Facades\Event::class,
        'File'      => Illuminate\Support\Facades\File::class,
        'Gate'      => Illuminate\Support\Facades\Gate::class,
        'Hash'      => Illuminate\Support\Facades\Hash::class,
        'Lang'      => Illuminate\Support\Facades\Lang::class,
        'Log'       => Illuminate\Support\Facades\Log::class,
        'Mail'      => Illuminate\Support\Facades\Mail::class,
        'Password'  => Illuminate\Support\Facades\Password::class,
        'Queue'     => Illuminate\Support\Facades\Queue::class,
        'Redirect'  => Illuminate\Support\Facades\Redirect::class,
        'Redis'     => Illuminate\Support\Facades\Redis::class,
        'Request'   => Illuminate\Support\Facades\Request::class,
        'Response'  => Illuminate\Support\Facades\Response::class,
        'Route'     => Illuminate\Support\Facades\Route::class,
        'Schema'    => Illuminate\Support\Facades\Schema::class,
        'Session'   => Illuminate\Support\Facades\Session::class,
        'Storage'   => Illuminate\Support\Facades\Storage::class,
        'URL'       => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View'      => Illuminate\Support\Facades\View::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        //HW
        'Form'      => Collective\Html\FormFacade::class,
        'Html'      => Collective\Html\HtmlFacade::class,        
        'Image' => Intervention\Image\Facades\Image::class,
        'Breadcrumbs' => DaveJamesMiller\Breadcrumbs\Facade::class,        
        'LaravelLocalization'   => Mcamara\LaravelLocalization\Facades\LaravelLocalization::class,
        'Honeypot' => Msurguy\Honeypot\HoneypotFacade::class,
        'Analytics' => Spatie\Analytics\AnalyticsFacade::class,
        'PDF' => Barryvdh\Snappy\Facades\SnappyPdf::class,
        'SnappyImage' => Barryvdh\Snappy\Facades\SnappyImage::class,
        'Cart' => App\Services\Cart\CartExtensionFacade::class,
        'ProductInfo' => App\Services\Facades\ProductInfoFacade::class,
        'Product' => App\Services\Facades\ProductFacade::class,
        'ProdAttribute' => App\Services\Facades\ProductAttributeFacade::class,
        'ProdPrice' => App\Services\Facades\ProductPriceFacade::class,
        'ProdCategory' => App\Services\Facades\ProductCategoryFacade::class,
        'ProdDimension' => App\Services\Facades\ProductDimensionFacade::class,
        'Part' => App\Services\Facades\PartFacade::class,
        'PartCategory' => App\Services\Facades\PartCategoryFacade::class,
        'ProdCode' => App\Services\Facades\ProductCodeFacade::class,
        'MobilPay' => App\Services\Facades\MobilPayFacade::class,
        'SchemaOrg' => App\Services\Facades\SchemaOrgFacade::class,
        'Omnipay' => Omnipay\Omnipay::class,

    ],

     'debug_blacklist' => [
        '_COOKIE' => array_diff(array_keys($_COOKIE), array()),
        '_ENV' => array_diff(array_keys($_ENV), array()), 

        '_SERVER' => [
            'APP_KEY',
            'APP_BASE_PATH',

            'SCRIPT_FILENAME',

            'PUSHER_APP_KEY',
            'PUSHER_APP_SECRET',

            'REDIS_HOST',
            'REDIS_PASSWORD',
            'REDIS_PORT',

            'MAIL_USERNAME',
            'DEFAULT_EMAIL',
            'MAIL_DRIVER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_ENCRYPTION',
            'MAIL_FROM_NAME',
            'MAIL_PASSWORD',

            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',

            'SERVER_ADDR',
            'SERVER_PORT',
            'REMOTE_ADDR',
            'DOCUMENT_ROOT',
            'REMOTE_PORT',
            'ORIG_PATH_TRANSLATED',

            'TZ',
            'REDIRECT_REDIRECT_UNIQUE_ID',
            'REDIRECT_REDIRECT_SCRIPT_URL',
            'REDIRECT_REDIRECT_SCRIPT_URI',
            'REDIRECT_REDIRECT_HTTPS',
            'REDIRECT_REDIRECT_SSL_TLS_SNI',
            'REDIRECT_REDIRECT_STATUS',
            'REDIRECT_UNIQUE_ID',
            'REDIRECT_SCRIPT_URL',
            'REDIRECT_SCRIPT_URI',
            'REDIRECT_HTTPS',
            'REDIRECT_SSL_TLS_SNI',
            'REDIRECT_HANDLER',
            'REDIRECT_STATUS',
            'UNIQUE_ID',
            'SCRIPT_URL',
            'SCRIPT_URI',
            'HTTPS',
            'SSL_TLS_SNI',
            'HTTP_HOST',
            'HTTP_CONNECTION',
            'HTTP_CACHE_CONTROL',
            'HTTP_UPGRADE_INSECURE_REQUESTS',
            'HTTP_USER_AGENT',
            'HTTP_ACCEPT',
            'HTTP_REFERER',
            'HTTP_ACCEPT_ENCODING',
            'HTTP_ACCEPT_LANGUAGE',
            'HTTP_COOKIE',
            'HTTP_X_HTTPS',
            'PATH',
            'SERVER_SOFTWARE',
            'SERVER_NAME',
            'REQUEST_SCHEME',
            'CONTEXT_PREFIX',
            'CONTEXT_DOCUMENT_ROOT',
            'SERVER_ADMIN',
            'REDIRECT_URL',
            'GATEWAY_INTERFACE',
            'SERVER_PROTOCOL',
            'REQUEST_METHOD',
            'QUERY_STRING',
            'REQUEST_URI',
            'SCRIPT_NAME',
            'ORIG_SCRIPT_FILENAME',
            'ORIG_PATH_INFO',
            'ORIG_SCRIPT_NAME',
            'PHP_SELF',
            'REQUEST_TIME_FLOAT',
            'REQUEST_TIME',
            'APP_NAME',
            'APP_ENV',
            'APP_DEBUG',
            'APP_LOG_LEVEL',
            'APP_URL',
            'APP_TIMEZONE',
            'BROADCAST_DRIVER',
            'CACHE_DRIVER',
            'SESSION_DRIVER',
            'QUEUE_DRIVER'

        ],
        
        '_POST' => [
            'password',
        ],
    ],

];