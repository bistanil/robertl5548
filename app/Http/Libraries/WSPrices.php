<?php

namespace App\Http\Libraries;
use App;
use App\Http\Libraries\Elitws;
use App\Models\ProductPrice;
use DateTime;

Class WSPrices{

	protected $products;

	public function __construct($products)
	{
		$this->products = $products;
	}

	public function autonet()
	{
		foreach ($this->products as $product) {
			if (!isset($product->title)) $product = $product->product;
			$prices = ProductPrice::whereProduct_id($product->id)->whereSource('Autonet')->get();
			if ($this->testPrice($prices))
			{
				$ws = new Autonetws();
				$ws->setProduct($product);
				$ws->process();
			}
		}
	}

	public function elit()
	{
		foreach ($this->products as $product) {
			if (!isset($product->title)) $product = $product->product;
			$prices = ProductPrice::whereProduct_id($product->id)->whereSource('Elit')->get();
			if ($this->testPrice($prices))
			{
				$ws = new Elitws();
				$ws->setProduct($product);
				$ws->process();
			}			
		}
	}

	public function testPrice($prices)
	{
		if ($prices->count() == 0) return TRUE;
		$price = $prices->first();
		$priceDate = new DateTime($price->updated_at);
		$now = new DateTime();		
		if ($priceDate->diff($now)->d > 0) return TRUE;
		return FALSE;
	}

}