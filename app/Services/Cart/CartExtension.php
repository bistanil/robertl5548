<?php

namespace App\Services\Cart;

use Gloudemans\Shoppingcart\Cart as InitialCart;
use App;
use Auth;
use Illuminate\Support\Collection;

Class CartExtension extends InitialCart{

	protected $instance;

	public function convertedSubtotal()
	{
		$content = $this->getContent();
		$subTotal = $content->reduce(function ($subTotal, $cartItem) {
			return $subTotal + ($cartItem->qty * exchangeConvert($cartItem->options->productCurrency, session()->get('frontCurrency')->id, $cartItem->price));
	    }, 0);	    	    
	    return $subTotal;
	}

}