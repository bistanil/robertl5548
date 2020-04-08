<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class MobilPayFacade extends Facade {

    protected static function getFacadeAccessor() 
    { 
    	return 'MobilPay';
    }
}