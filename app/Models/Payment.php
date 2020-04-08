<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'company','amount','currency','gateway', 'started_at', 'response_time', 'response_code', 'response', 'type', 'order_id'
    ];

    public function order()
    {
    	return $this->belongsTo('App\Models\Order', 'order_id');
    }

    public function gateway()
    {
    	return $this->belongsTo('App\Models\Gateway', 'gateway', 'type');
    }

    public function currencyInfo()
    {
        return $this->belongsTo('App\Models\Currency', 'currency');
    }
}
