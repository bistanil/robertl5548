<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCar extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'type_id','vin', 'power','registration_number'
    ];

    public function type()
    {
        return $this->belongsTo('App\Models\CarModelType');
    }
}
