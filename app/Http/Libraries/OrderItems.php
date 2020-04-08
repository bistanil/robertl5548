<?php

namespace App\Http\Libraries;
use App;
use Cart;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\ProductPrice;
use App\Http\Libraries\CartDiscounter;

Class OrderItems{

	protected $order;
	protected $currency;
	protected $instance;
	protected $request;
	protected $client;

	public function __construct($request, Order $order, $currency, $instance = 'default', $client = null)
	{
		$this->order = $order;
		$this->currency = $currency;
		$this->instance = $instance;
		$this->request = $request;
		$this->client = $client;
	}

	public function saveItems()
	{		
		$this->deleteOrderItems();
		foreach (Cart::instance($this->instance)->content() as $key => $item) 
			$this->saveItem($item);		
	}

	private function saveItem($item)
	{
		$discounter = new CartDiscounter($this->request, $this->instance, $this->client);
		$price = productPrice($discounter->getProduct($item->id));
		if ($price != null)
		{			
			$product = $discounter->getProduct($item->id);
			$orderItem = new OrderItem();
			$orderItem->order_id = $this->order->id;
			$orderItem->product_id = $item->id;
			$orderItem->title = $item->name;
			$orderItem->product_title = $product->title;
			$orderItem->product_code = $product->code;
			$orderItem->qty = $item->qty;
			$orderItem->price_source = $price->source;
			if ($product->manufacturer != null)
			{
				$orderItem->manufacturer_id = $product->manufacturer_id;
				$orderItem->manufacturer_title = $product->manufacturer->title;
			}
			$orderItem->currency = $this->currency->code;
			$orderItem->unit_price = finalPrice($product);
			$orderItem->price = floatval($item->price);
			if($item->options->priceId == 0) $orderItem->price_id = $price->id;
			else $orderItem->price_id = $item->options->priceId;			
			$orderItem->unit_discount = $discounter->itemDiscountValue($item->id, $item->price);
			$orderItem->subtotal_discount = $discounter->itemDiscountSubtotal($item->id, $item->price, $item->qty);
			$orderItem->discount_percentage = $discounter->itemDiscountPercentage($item->id, $item->price, $item->qty);
			$orderItem->subtotal_list_price = finalPrice($product)*$item->qty;
			$orderItem->subtotal = $orderItem->subtotal_list_price - $orderItem->subtotal_discount;
			$orderItem->save();
		}		
	}

	private function deleteOrderItems()
    {
        OrderItem::where('order_id', '=', $this->order->id)->delete();
    }

}