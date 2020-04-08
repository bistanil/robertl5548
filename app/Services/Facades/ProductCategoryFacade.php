<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class ProductCategoryFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'ProdCategory';
    }
}