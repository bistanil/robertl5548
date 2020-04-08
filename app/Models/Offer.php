<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Offer extends Model
{
    protected $fillable = [
					'client_id',
					'name',
					'email',
					'phone',
					'car',
					'vin',
					'total',
					'currency',
					'title',
					'content',
					'footer_text',
					'expiration_date',
    		];
    protected $dates = ['created_at', 'updated_at', 'expiration_date'];

    public function getExpirationDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-M-Y');
    }

    public function setExpirationDateAttribute($value)
    {
        $this->attributes['expiration_date'] = Carbon::parse($value);
    }

    public function client()
    {
    	return $this->belongsTo('App\Models\Client');
    }

    public function company()
    {
    	return $this->belongsTo('App\Models\Company');
    }

    public function items()
    {
        return $this->hasMany('App\Models\OfferItem');
    }

    public function routeNotificationFor()
    {
        return $this->email;
    }
}
