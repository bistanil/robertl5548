<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;

    protected static $sortableGroupField = 'product_id';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'active', 'position','type'
    ];

    public function product()
    {
    	return $this->belongsTo('App\Models\CatalogProduct');
    }
}
