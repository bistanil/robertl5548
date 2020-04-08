<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class ProductAttributeFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'ProdAttribute';
    }
}