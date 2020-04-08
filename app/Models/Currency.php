<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'code', 'symbol', 'default'
    ];

    public function rates($id)
    {
    	return $this->find($id)->hasMany('App\Models\Exchange', 'currency1');
    }

    public function byCode($code)
    {
        return $this->whereCode($code)->first();
    }

    public static function defaultCurrency()
    {
        $currency = Currency::whereDefault('yes')->get()->first();
        if (isset($currency->code)) return $currency->symbol;
        else return '';

    }

}
