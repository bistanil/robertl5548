<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class PartFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'Part';
    }
}