<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientNote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'order_id', 'title', 'note'
    ];

    public function client()
    {
    	return $this->belongsTo('App\Models\Client');
    }

    public function user()
    {
    	return $this->belongsTo('App\User');
    }
}
