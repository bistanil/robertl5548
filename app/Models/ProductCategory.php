<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
	use \Rutorika\Sortable\SortableTrait;
    //
	protected static $sortableGroupField = 'category_id';

    protected $table='catalog_category_product';

    protected $fillable = [
        'category_id', 'product_id', 'position'
    ];

    public function category()
    {
    	return $this->belongsTo('App\Models\CatalogCategory','category_id', 'id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\CatalogProduct','id','product_id');
    }
}
