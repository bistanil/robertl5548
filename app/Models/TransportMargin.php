<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportMargin extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency_id', 'active', 'margin', 'min', 'max','type_id'
    ];

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\TransportType');
    }

}