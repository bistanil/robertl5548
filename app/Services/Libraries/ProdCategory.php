<?php namespace App\Services\Libraries;

use App;
use App\Models\ProductCategory;

class ProdCategory{

	public function store($request, $product)
	{
		$status = true;
		foreach ($request['categories'] as $categoryId) if ($this->storeCategory($product, $categoryId) == false) $status = false;        
		return $status;
	}

	public function update($request, $product)
	{
		$this->destroy($product);
		return $this->store($request, $product);
	}

	public function destroy($product)
	{
		return ProductCategory::whereProduct_id($product->id)->delete();
	}

	private function storeCategory($product, $categoryId)
	{
		$category = new ProductCategory();
        $category->category_id = $categoryId;
        $category->product_id = $product->id;
        return $category->save();
	}	

}