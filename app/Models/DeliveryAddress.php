<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAddress extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'name', 'phone','address', 'county', 'city', 'postal_code'
    ];

    public function client()
    {
    	return $this->belongsTo('App\Models\Client');
    }

}
