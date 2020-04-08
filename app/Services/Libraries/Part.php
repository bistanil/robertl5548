<?php namespace App\Services\Libraries;

use App;
use App\Models\CatalogProduct;
use ProdPrice;
use ProdDimension;
use PartCategory;
use ProdCode;

class Part{

	public function store($request, $isImport = false)
	{
		$status = true;
		$product = new CatalogProduct($request);		
		$product->language = $product->language;
		$product->search_code = preg_replace("/[^a-zA-Z0-9]+/","", $product->code);
		$product->slug = str_slug($request['title'].'-'.$request['code'], "-");
		if ($product->save() == false) $status = false;	
		if (PartCategory::store($request, $product) == false)  $status = false;	
		//if (ProdDimension::store($request, $product) == false) $status = false;
		if (ProdPrice::store($request, $product) == false) $status = false;	
		if (ProdCode::store($request, $product) == false) $status = false;	
		if ($isImport) return $product;
		return $status;		
	}

	public function update($request, $product, $isImport = false)
	{
		$status = true;
		$product->catalog_id = 0;
		$product->save();
		if ($product->update($request) == false) $status = false;				
		$product->search_code = preg_replace("/[^a-zA-Z0-9]+/","", $product->code);
		$product->slug = str_slug($request['title'].'-'.$request['code'], "-");
		if ($product->save() == false) $status = false;
		if (PartCategory::update($request, $product) == false)  $status = false;
		//if (ProdDimension::update($request, $product) == false) $status = false;
		if (array_key_exists('import', $request))	
			if (ProdPrice::update($request, $product) == false) $status = false;
		if (ProdCode::update($request, $product) == false) $status = false;								
		if ($isImport) return $product;		
		return $status;
	}

}