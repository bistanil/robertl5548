<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active', 'type', 'show_delivery_address'
    ];

    public function margins()
    {
        return $this->hasMany('App\Models\TransportMargin', 'type_id');
    }


}
