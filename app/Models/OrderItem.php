<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 
        'product_id',
        'price_id',         
        'title',
        'qty',
        'unit_price',
        'price',
        'subtotal',
        'currency',
        'unit_discount',
        'subtotal_list_price',
        'subtotal_discount',
        'discount_percentage',
        'price_source',
        'manufacturer_id',
        'manufacturer_title', 
        'product_title',
        'product_code'       
    ];

    public function order()
    {
    	return $this->belongsTo('App\Models\Order');
    }

    public function priceInfo()
    {
        return $this->hasOne('App\Models\ProductPrice', 'id', 'price_id');
    }

    public function product()
    {
    	return $this->belongsTo('App\Models\CatalogProduct');
    }

    public function warranties()
    {
        return $this->hasMany('App\Models\Warranty');
    }
}
