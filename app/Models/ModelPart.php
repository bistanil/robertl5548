<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelPart extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'model_id', 'part_id'
    ];

    public function model()
    {
    	return $this->belongsTo('App\Models\CarModel');
    }

    public function part()
    {
    	return $this->belongsTo('App\Models\CatalogProduct', 'part_id');
    }
}