<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OfferRequest extends Model
{
    use Notifiable;

    protected $fillable = [
        'language','email','name','phone','content','status', 'car_id', 'model_id', 'type_id', 'vin', 'accept_terms', 'accept_policy','second_status'
    ];

    public function type()
    {
    	return $this->belongsTo('App\Models\CarModelType');
    }
}
