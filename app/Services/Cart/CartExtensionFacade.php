<?php

namespace App\Services\Cart;

use Illuminate\Support\Facades\Facade;

class CartExtensionFacade extends Facade {

    protected static function getFacadeAccessor() { 
    	return 'cart'; 
    }
}