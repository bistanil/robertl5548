<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFeed extends Model
{
     protected $table='products_feed';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'feed_id'
    ];

}
