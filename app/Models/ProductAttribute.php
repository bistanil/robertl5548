<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    //
	protected $table='catalog_attribute_product';

    protected $fillable = [
        'product_id', 'product_attribute', 'value'
    ];

    public function attribute()
    {
    	return $this->hasOne('App\Models\CatalogAttribute', 'id', 'attribute_id');
    }

    public function listItem()
    {
	return $this->hasOne('App\Models\CatalogListItem', 'id', 'value');
    }
}
