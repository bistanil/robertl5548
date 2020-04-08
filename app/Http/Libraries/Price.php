<?php

namespace App\Http\Libraries;
use App\Models\PriceMargin;
use Auth;

Class Price{

	protected $categories;

	public function productPrice($product)
	{
		return $this->getPrice($product);
	}

	public function finalPrice($product)
	{
		$price = $this->getPrice($product);
		if ($price == null) return $price;
		if ($price->source == 'admin') return round($price->price);
		if ($price->source == 'temporary') return round($price->price);		
		$margin = $this->margin($product, $price);
		if ($margin != null) $finalPrice = $price->price*$margin->margin;
		else $finalPrice = $price->price;
		return round($finalPrice);			
	}

	public function salePrice($product, $price)
	{
		$margin = $this->margin($product, $price);
		if ($margin != null) $finalPrice = $price->price*$margin->margin;
		else $finalPrice = $price->price;
		return round($finalPrice);	
	}

	public function discountedPrice($product)
	{
		if (Auth::guard('client')->check())
		{ 
			$discount = Auth::guard('client')->user()->discounts->first();
			if ($discount != null)
			{
				$productPrice = $this->finalPrice($product);
				return round($productPrice * $discount->discount);
			}
		}
		return $this->finalPrice($product);
	}

	private function margin($product, $price)
	{
		$price = $price->price;		
		$this->categoriesIds($product);		
		$margin = PriceMargin::whereActive('active')
							 ->where('min', '<=', $price)
							 ->where('max', '>', $price)
							 ->where('manufacturer_id', '=', $product->manufacturer_id)
							 ->whereIn('category_id', $this->categories)
							 ->orderBy('margin', 'ASC')
							 ->first();
		if ($margin != null) return $margin;
		$margin = PriceMargin::whereActive('active')
							 ->where('min', '<=', $price)
							 ->where('max', '>', $price)
							 ->where('manufacturer_id', '=', 0)
							 ->whereIn('category_id', $this->categories)
							 ->orderBy('margin', 'ASC')
							 ->first();
		if ($margin != null) return $margin;
		$margin = PriceMargin::whereActive('active')
							 ->where('min', '<=', $price)
							 ->where('max', '>', $price)
							 ->where('manufacturer_id', '=', $product->manufacturer_id)
							 ->where('category_id', '=', 0)
							 ->orderBy('margin', 'ASC')
							 ->first();
		if ($margin != null) return $margin;
		$margin = PriceMargin::whereActive('active')
							 ->where('min', '<=', $price)
							 ->where('max', '>', $price)
							 ->where('manufacturer_id', '=', 0)
							 ->where('category_id', '=', 0)
							 ->orderBy('margin', 'ASC')
							 ->first();
		if ($margin != null) return $margin;
		else return null;

	}

	private function categoriesIds($product)
	{
		foreach ($product->partsCategories as $category) {
			$this->categories[] = $category->id;
		}
		if($this->categories == null) $this->categories = [];
	}

	private function getPrice($product)
	{
		$prices = $product->prices()->whereSource('admin')->get();
		if ($prices->count() > 0) return $prices->first();
		$prices = $product->prices()->whereSource('temporary')->get();
		if ($prices->count() > 0) return $prices->first();
		$prices = $product->prices()->orderBy('price')->get();
		if ($prices->count() > 0) return $prices->first();
		return null;
	}

}