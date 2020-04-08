<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPart extends Model
{
    //
    public $timestamps = false;
    protected $fillable = [
        'category_id', 'part_id'
    ];
}
