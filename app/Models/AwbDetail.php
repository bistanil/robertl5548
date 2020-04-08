<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwbDetail extends Model
{
         /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
            'order_id',
            'awb_number',
    ];

    public function order()
    {
    	return $this->belongsTo('App\Models\Order');
    }
}
