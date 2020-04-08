<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferItem extends Model
{
    protected $fillable = [
					'offer_id',
					'product_id',
					'product_title',
					'product_code',
					'price_id',
					'qty',
					'unit_price',
					'subtotal_unit_price',
					'currency',
    		];

    public function offer()
    {
    	return $this->belongsTo('App\Models\Offer');
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
