<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wishlist_id', 
        'product_id',
        'price_id',         
        'title',
        'qty',
        'unit_price',
        'subtotal',
        'currency'
    ];

    public function wishlist()
    {
    	return $this->belongsTo('App\Models\Wishlist');
    }

    public function priceInfo()
    {
        return $this->hasOne('App\Models\ProductPrice', 'id', 'price_id');
    }

    public function product()
    {
    	return $this->belongsTo('App\Models\CatalogProduct');
    }
}
