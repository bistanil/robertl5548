<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class PartCategoryFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'PartCategory';
    }
}