<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    //
	protected $table = 'catalog_price_product';

    protected $fillable = [
        'price', 'price_no_vat','old_price', 'source', 'currency_id', 'product_id', 'acquisition_price_no_vat','acquisition_price', 'old_price', 'stock_no', 'supplier_id'
    ];

    public function currency()
    {
    	return $this->hasOne('App\Models\Currency','id','currency_id');
    }

    public function sourceTotal($source)
    {
    	return $this->whereSource($source)->count();
    }

    public function supplier()
    {
        return $this->hasOne('App\Models\Supplier','id','supplier_id');
    }

    public function supplierTotal($supplier)
    {
        return $this->whereSupplier_id($supplier)->count();
    }

    public function updatedAt($supplier)
    {
        return $this->whereSupplier_id($supplier->id)->pluck('updated_at');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\CatalogProduct');
    }
    
}
