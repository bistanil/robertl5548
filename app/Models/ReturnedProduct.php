<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ReturnedProduct extends Model
{
    use Notifiable;
    
    protected $fillable = [
        'client_id','email','name','phone','product_codes','order_number','status','reason', 'return_back', 'accept_terms', 'accept_policy', 'accept_return','second_status'
    ];

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}
