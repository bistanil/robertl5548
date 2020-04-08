<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCompany extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'title', 'fiscal_code','registration_number','bank','bank_account', 'address'
    ];

    public function client()
    {
    	return $this->belongsTo('App\Models\Client');
    }
}
