<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
    
    'api/admin-set-item-active-inactive',
    'api/admin-set-item-approve-reject',

    'api/admin-settings-update-label',
    'api/admin-settings-create-label',

    'api/admin-get-client',
    'api/admin-client-addresses-companies-lists',
    'api/admin-client-discount',

    'api/admin-get-cities',
    'api/admin-get-cities-order',


    'api/admin-order-transport-cost',
    'api/admin-order-update-total',
    
    'api/admin-order-update-total',
    
    'api/admin-cars-models-list',
    'api/admin-models-types-list',
    'api/admin-sidebar-cars-models-list',
    'api/admin-sidebar-cars-models-list-motorcycles',
    'api/admin-sidebar-cars-models-list-trucks',
    'api/admin-sidebar-cars-models-list-other',
    'api/admin-sidebar-models-types-list',
    'api/admin-sidebar-models-types-list-motorcycles',
    'api/admin-sidebar-models-types-list-trucks',
    'api/admin-sidebar-models-types-list-other',
    
    'api/admin-feed-products-add',
    'api/admin-feed-products-delete',
    
    'api/front-get-cities',

    'api/request-offer-cars-models-list',
    'api/request-offer-models-types-list',
    'api/sidebar-cars-models-list',
    'api/get-model-types-path',
    'api/sidebar-models-types-list',
    'api/sidebar-models-fuel-list',

    'api/front-order-transport-cost',
    'api/front-order-update-total',
    'api/front-order-free-delivery',
    'api/front-order-require-delivery-info',
    'api/front-get-cities-order',

    'api/front-product-messages',
    'api/front-suggestion-messages',
    'api/front-product-codes-list',
    'raspuns-mobilpay',
    ];
}
