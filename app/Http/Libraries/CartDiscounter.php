<?php

namespace App\Http\Libraries;
use App;
use Cart;
use App\Models\CatalogProduct;
use App\Models\Discount;

Class CartDiscounter{

	protected $request;
	protected $instance;
	protected $client;

	public function __construct($request = null, $instance, $client = null)
	{
		if ($request == null) $request = collect([]);
		$this->request = $request;
		$this->instance = $instance;
		$this->client = $client;
	}

	public function cartDiscountValue()
	{
		$discountValue = 0;
		foreach (Cart::instance($this->instance)->content() as $key => $item) $discountValue += $this->itemDiscountSubtotal($item->id, $item->price, $item->qty);
		return $discountValue;
	}

	public function discountedTotal()
	{
		if ($this->instance == 'default') return convertFormattedNumberToFloat(Cart::instance($this->instance)->subtotal) - $this->cartDiscountValue();
		return convertFormattedNumberToFloat(Cart::instance($this->instance)->subtotal);
	}

	public function cartTotal()
	{
		return $this->generalDiscountedPrice(convertFormattedNumberToFloat(Cart::instance($this->instance)->subtotal));
	}

	public function itemDiscountSubtotal($productId, $price, $qty)
	{
		return $qty*$this->itemDiscountValue($productId, $price);
	}

	public function itemDiscountValue($productId, $price)
	{
		$product = $this->getProduct($productId);		
		$discountedPrice = $this->clientDiscountedPrice($product, $price);		
		$discountedPrice = $this->generalDiscountedPrice($discountedPrice);
		return finalPrice($product) - $discountedPrice;

	}

	public function itemDiscountPercentage($productId, $price)
	{
		$discountValue = $this->itemDiscountValue($productId, $price);
		return round($discountValue/$price*100);
	}

	public function getProduct($productId)
	{
		return CatalogProduct::find($productId);
	}

	public function clientDiscountedPrice($product, $price)
	{
		if ($this->instance != 'default') return $price;
		if ($this->client == null) return $price;
		$discount = $this->client->discounts->first();
		if ($discount == null) return $price;
		return $price*$discount->discount;
	}

	public function generalDiscountedPrice($price)
	{
		//dd(!$this->request->has('discount'));
		if (!$this->request->has('discount')) return $price;
		$discount = Discount::find($this->request->discount);
		if ($discount != null) return $price*$discount->discount;
		return $price;
				
	}

}