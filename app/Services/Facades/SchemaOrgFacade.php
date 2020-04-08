<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class SchemaOrgFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'SchemaOrg';
    }
}