<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class ProductFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'Product';
    }
}