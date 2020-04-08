<?php

namespace App\Http\Libraries;

use App\Models\CatalogProduct;
use App\Models\Manufacturer;
use App\Models\ProductPrice;
use App\Models\Currency;
use File;
use DB;

Class PriceImport{

	protected $prices;
	
	public function __construct($prices)
	{
		$this->prices = $prices;		
	}

	public function save()
	{
		DB::connection()->disableQueryLog();
		DB::table('prices_temp')->insert($this->prices);
		DB::connection()->enableQueryLog();
	}

	private function manufacturer()
	{
		return Manufacturer::whereTitle($this->item->manufacturer)->get()->first();
	}

	private function currency()
	{
		return Currency::whereDefault('yes')->get()->first();
	}

}