<?php namespace App\Services\Libraries;

use App\Models\ProductDimension;

/**
* Process product price info
*/

class ProdDimension{
	
	public function store($request, $product)
	{		
        $dimensions = new ProductDimension($request);         
        $dimensions->product_id = $product->id;
        if ($dimensions->weight != null || $dimensions->height != null || $dimensions->width != null || $dimensions->length != null) $dimensions->save();	    
		return true;                   
	}

	public function update($request, $product)
	{
		$dimensions = ProductDimension::whereProduct_id($product->id)->get()->first();
		if ($request['weight'] == '' && $request['height'] == '' && $request['width'] == '' && $request['length'] == '') $this->destroy($product);		
		if ($dimensions == null) return $this->store($request, $product);
		return $dimensions->update($request);
	}

	public function destroy($product)
	{
		return ProductDimension::whereProduct_id($product->id)->delete();
	}

}