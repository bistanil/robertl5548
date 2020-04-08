<?php namespace App\Services\Libraries;

use App;
use App\Models\CatalogProduct;
use App\Models\Catalog;
use ProdAttribute;
use ProdPrice;
use ProdDimension;
use ProdCategory;
use ProdCode;

class Product{

	public function store($request, $isImport = false)
	{
		$status = true;
		$product = new CatalogProduct($request);
		$catalog = Catalog::find($request['catalog_id']);
		$product->language = $catalog->language;
		$product->catalog_id = $catalog->id;
		$product->type = 'catalog';
		$product->search_code = preg_replace("/[^a-zA-Z0-9]+/","", $product->code);
		$product->slug = str_slug($request['title'].'-'.$request['code'], "-");
		if ($product->save() == false) $status = false;
		if (ProdCategory::store($request, $product) == false) $status = false;
		//if (ProdDimension::store($request, $product) == false) $status = false;
		if (ProdPrice::store($request, $product) == false) $status = false;
		if (ProdAttribute::store($request, $product) == false) $status = false;	
		if (ProdCode::store($request, $product) == false) $status = false;
		if ($isImport) return $product;
		return $status;		
	}

	public function update($request, $product, $isImport = false)
	{
		$status = true;
		$product->save();
		if ($product->update($request) == false) $status = false;				
		$product->search_code = preg_replace("/[^a-zA-Z0-9]+/","", $product->code);
		$product->slug = str_slug($request['title'].'-'.$request['code'], "-");
		if ($product->save() == false) $status = false;		
		if (ProdCategory::update($request, $product) == false) $status = false;		
		//if (ProdDimension::update($request, $product) == false) $status = false;	
		if (array_key_exists('import', $request))
			if (ProdPrice::update($request, $product) == false) $status = false;
		if (ProdAttribute::update($request, $product) == false) $status = false;
		if (ProdCode::update($request, $product) == false) $status = false;
		if ($isImport) return $product;
		return $status;
	}

}