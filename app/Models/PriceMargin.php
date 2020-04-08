<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceMargin extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active', 'margin', 'min', 'max', 'manufacturer_id', 'category_id'
    ];

    public function manufacturer()
    {
    	return $this->belongsTo('App\Models\Manufacturer');
    }

    public function category()
    {
    	return $this->belongsTo('App\Models\PartsCategory');
    }

}
