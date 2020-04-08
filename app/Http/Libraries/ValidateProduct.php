<?php

namespace App\Http\Libraries;
use App\Models\CatalogProduct;

Class ValidateProduct{

	protected $productId;

	public function __construct($productId)
	{
		$this->productId = $productId;
	}

	public function validate()
	{
		$product = $this->getProduct();		
		if ($product == null) return FALSE;
		if ($product->active != 'active') return FALSE;
		return TRUE;
	}

	private function getProduct()
	{
		return CatalogProduct::find($this->productId);
	}

}