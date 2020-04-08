<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class County extends Model
{
     //
    protected $fillable = [
        'active','title'
    ];

    public function cities()
    {
    	return $this->hasMany('App\Models\City');
    }
}
