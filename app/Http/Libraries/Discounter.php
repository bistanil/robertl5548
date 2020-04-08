<?php

namespace App\Http\Libraries;
use App\Models\Discount;

Class Discounter{

	public static function apply($value, $discountId)
	{
		$discount = Discount::find($discountId);
		if (isset($discount->discount)) return $value*$discount->discount;
		else return $value;
	}

}