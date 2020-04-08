<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
     //
    protected $fillable = [
        'county_id', 'active','title'
    ];

    public function county()
    {
    	return $this->belongsTo('App\Models\County');
    }
}
