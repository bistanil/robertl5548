<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OrderInvoice extends Model
{
    use Notifiable;

    protected static $sortableGroupField = 'order_id';

    protected $fillable = [
        'order_id', 'client_id','client_email', 'client_name', 'title'
    ];

    public function order()
    {
    	return $this->belongsTo('App\Models\Order');
    }

    public function routeNotificationFor()
    {
        return $this->client_email;
    }
}
