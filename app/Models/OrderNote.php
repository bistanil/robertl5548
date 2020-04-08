<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'order_id', 'title', 'note'
    ];

    public function order()
    {
    	return $this->belongsTo('App\Models\Order');
    }

    public function user()
    {
    	return $this->belongsTo('App\User');
    }
}
