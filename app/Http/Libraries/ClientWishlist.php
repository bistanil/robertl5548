<?php

namespace App\Http\Libraries;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\ProductPrice;
use App\Models\CatalogProduct;
use App\Http\Libraries\CartExtension;
use Cart;

Class ClientWishlist{

	protected $client;
	protected $list;

	public function __construct($client)
	{
		$this->client = $client;
	}

	public function persist()
	{
		$this->setList();
		$this->removeItems();
		$this->saveItems();
	}

	public function populate()
	{
		$this->setList();
		$wishlist = Cart::instance('wishlist')->content();
		foreach ($this->list->items as $key => $item) {
			if ($wishlist->search(function ($cartItem, $rowId) use ($item) {				
					return intval($cartItem->id) == intval($item->product_id);
				}) === false) Cart::instance('wishlist')->add(['id' => $item->product_id, 'name' => $item->title, 'qty' => 1, 'price' => $item->unit_price, 'options' => ['priceId' => $item->price_id]]);
		}
		$this->persist();
	}

	private function saveItems()
	{
		foreach (Cart::instance('wishlist')->content() as $key => $item) {
			$product = CatalogProduct::find($item->id);
			if ($product != null)
			{
				$price = ProductPrice::find($item->options->priceId);
				if ($price != null) $currency = $price->currency;
				if (isset($currency))
				{
					$listItem = new WishlistItem();
					$listItem->wishlist_id = $this->list->id;
					$listItem->product_id = $item->id;
					$listItem->title = $item->name;
					$listItem->qty = 1;
					$listItem->unit_price = $item->price;
					$listItem->subtotal = $item->subtotal;
					$listItem->currency = $currency->code;
					$listItem->price_id = $item->options->priceId;
					$listItem->save();
				}				
			}
		}
	}

	private function removeItems()
	{
		WishlistItem::whereWishlist_id($this->list->id)->delete();
	}

	private function setList()
	{
		$list = Wishlist::whereClient_id($this->client->id)->get()->first();
		$cartExtension = new CartExtension();
		$cartExtension->setInstance('wishlist');
		if ($list == null)
		{
			$list = new Wishlist();
			$list->client_id = $this->client->id;
			$list->total = $cartExtension->subtotal();
			$list->save();
			$this->list = $list;
		} else {
			$list->total = $cartExtension->subtotal();
			$list->save();
			$this->list = $list;
		}
	}

}