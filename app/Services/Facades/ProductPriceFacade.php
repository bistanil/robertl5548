<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class ProductPriceFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'ProdPrice';
    }
}