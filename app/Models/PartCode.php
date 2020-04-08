<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartCode extends Model
{
    //
    protected $fillable = [
        'part_id', 'code'
    ];

    public function product()
    {
    	return $this->belongsTo('App\Models\CatalogProduct', 'part_id', 'id');
    }
}
