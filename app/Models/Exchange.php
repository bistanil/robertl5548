<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency1', 'currency2','rate'
    ];

    public function currency()
    {
    	return $this->belongsTo('App\Models\Currency','currency1','id');
    }

    public function secondCurrency()
    {
    	return $this->belongsTo('App\Models\Currency','currency2','id');
    }

    public function rate($currency1, $currency2)
    {
    	return $this->where('currency1', '=', $currency1)->where('currency2', '=', $currency2)->first();
    }
}
