<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedProduct extends Model
{
    protected $fillable = [
        'file_name', 'feed_id', 'catalog', 'cars', 'models', 'types', 'manufacturer_id', 'feed_prices', 'min_price', 'max_price', 'description_title', 'description', 'use_additional_title'
    ];

    public function feed()
    {
    	return $this->hasOne('App\Models\Feed', 'id', 'feed_id');
    }
}
