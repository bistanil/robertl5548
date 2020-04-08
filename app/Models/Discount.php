<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'discount'
    ];

    public function client()
    {
    	return $this->belongsTo('App\Models\Client');
    }
}
