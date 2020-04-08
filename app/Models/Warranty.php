<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Warranty extends Model
{
     use Notifiable;

    protected static $sortableGroupField = 'order_id';

    protected $fillable = [
        'order_id', 'product_id','product_title', 'qty', 'invoice_no', 'client_id','client_email', 'client_name', 'title', 'start_date', 'expiration_date'
    ];

    protected $dates = ['created_at', 'updated_at', 'start_date', 'expiration_date'];

    public function getStartDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function setStartDateAttribute($value)
    {
    	$this->attributes['start_date'] = Carbon::parse($value);
    }

    public function getExpirationDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function setExpirationDateAttribute($value)
    {
        $this->attributes['expiration_date'] = Carbon::parse($value);
    }

    public function order()
    {
    	return $this->belongsTo('App\Models\Order');
    }

    public function routeNotificationFor()
    {
        return $this->client_email;
    }
}
