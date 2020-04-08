<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDimension extends Model
{
    //use SoftDeletes;
    
    protected $table='product_dimensions';
    
    protected $fillable = [
        'product_id', 'weight', 'width', 'height', 'length'
    ];
}
