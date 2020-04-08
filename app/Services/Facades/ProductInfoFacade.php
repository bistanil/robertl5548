<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class ProductInfoFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'ProductInfo';
    }
}