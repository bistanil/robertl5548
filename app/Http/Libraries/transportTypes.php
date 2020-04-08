<?php

namespace App\Http\Libraries;
use App\Models\TransportMargin;

Class TransportTypes {

	public static function apply($value, $typeId)
	{
		$discount = TransportMargin::where('type_id',$typeId)->get()->first();
		return $value;
	}

}