<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartOriginalCode extends Model
{
    //
    protected $fillable = [
        'part_id', 'brand', 'code'
    ];
}
