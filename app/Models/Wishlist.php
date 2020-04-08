<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id'
    ];

    public function items()
    {
    	return $this->hasMany('App\Models\WishlistItem');
    }

    public function client()
    {
    	return $this->belongsTo('App\Models\Client');
    }
}
