<?php namespace App\Services\Libraries;

use App;
use App\Models\ProductAttribute;
use App\Models\Catalog;

class ProdAttribute{

	public function store($request, $product)
	{
		$status = true;	
		if ($this->catalog($product->catalog_id) != null)
		{
			$attributes = $this->catalog($product->catalog_id)->attributes()->where('active', '=', 'active')->get();
			foreach ($attributes as $attribute) if ($this->storeAttribute($request, $product, $attribute) == false) $status = false;        
			return $status;
		}
		return false;
	}

	public function update($request, $product)
	{
		$this->destroy($product);
		return $this->store($request, $product);
	}

	public function destroy($product)
	{
		return ProductAttribute::whereProduct_id($product->id)->delete();
	}

	private function storeAttribute($request, $product, $attribute)
	{
		$productAttribute = new ProductAttribute();        
        $productAttribute->product_id = $product->id;
        $productAttribute->attribute_id = $attribute->id;
        $productAttribute->value = $request[$attribute->id];
        if ($productAttribute->value != '') return $productAttribute->save();
        return true;
	}

	private function catalog($catalogId)
	{
		return Catalog::find($catalogId);
	}

}