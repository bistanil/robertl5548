<?php namespace App\Services\Libraries;

use App\Models\ProductPrice;
use App\Models\Supplier;
use App\Models\Company;

/**
* Process product price info
*/

class ProdPrice{
	
	public function store($request, $product)
	{		
        $company = Company::whereDefault('yes')->get()->first();
		$tva = intval($company->vat_percentage)/100+1; 
		if ($request['supplier_id'] > 0)
        {
        	$supplier = Supplier::find($request['supplier_id']);
        	$price = new ProductPrice($request); 
        	$price->product_id = $product->id;
        	$price->source = str_slug($supplier->title, '_'); 
        	if ($price->price > 0)
        		$price['stock_no'] = $price->stock_no;
        		$price['acquisition_price_no_vat'] = floatval(str_replace(',', '.',$price->acquisition_price_no_vat));
				$price['price_no_vat'] = floatval(str_replace(',', '.',$price->price_no_vat));
				$price['old_price'] = floatval(str_replace(',', '.',$price->old_price));
        		{
        			if($supplier->title == 'Admin')
					{
						$price['acquisition_price'] = floatval(str_replace(',', '.',$price->acquisition_price_no_vat));
						$price['price'] = floatval(str_replace(',', '.',$price->price_no_vat));
					}
					if($supplier->title != 'Admin')
					{
						$price['acquisition_price'] = floatval(str_replace(',', '.',$price->acquisition_price_no_vat))*$tva;
						$price['price'] = floatval(str_replace(',', '.',$price->price_no_vat))*$tva;					
					}
				return $price->save(); 
        		}      
        	return true;
        }
        return true;
	}

	public function update($request, $product)
	{		
		$this->destroy($product, $request['supplier_id']);		
		return $this->store($request, $product);			
	}

	public function destroy($product, $supplier)
	{		
		ProductPrice::whereProduct_id($product->id)->whereSupplier_id($supplier)->delete();
		return true;
	}

}