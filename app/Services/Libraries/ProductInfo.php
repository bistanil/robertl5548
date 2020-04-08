<?php

namespace App\Services\Libraries;

use App\Models\CatalogProduct;
use Cart;

class ProductInfo{

	protected $product;

	public function __construct($product)
	{
		$this->product = $product;
	}

	public function title()
	{
		$productTitle = '';
		if ($this->product->code != null) $productTitle .= $this->product->code.' ';
		$productTitle .= $this->product->title.' ';
		if ($this->product->manufacturer != null) $productTitle .= $this->product->manufacturer->title.' ';
		return $productTitle;
	}

	public function titleWithoutCode()
	{
		$productTitle = '';
		$productTitle .= $this->product->title.' ';
		if ($this->product->manufacturer != null) $productTitle .= $this->product->manufacturer->title.' ';
		return $productTitle;
	}

}