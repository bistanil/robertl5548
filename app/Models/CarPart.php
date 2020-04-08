<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarPart extends Model
{
    //
    protected $fillable = [
        'car_id', 'part_id'
    ];
}
