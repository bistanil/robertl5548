<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePart extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'type_id', 'part_id', 'content'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\CatalogProduct', 'part_id', 'id');
    }

    
}