<?php

namespace App\Http\Libraries;
use App;
use Auth;
use Cart;

Class CartExtension extends Cart{

	protected $instance;

	public function setInstance($instance)
	{
		$this->instance = $instance;
	}

	public function subtotal()
	{
		$content = Cart::instance($this->instance)->content();

        $total = $content->reduce(function ($total, $cartItem) {
            return $total + ($cartItem->qty * $cartItem->price);
        }, 0);
        return $total;
	}

}