<?php

namespace App\Models\Elit;

use Illuminate\Database\Eloquent\Model;

class ElitCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_id', 'manufacturer_id', 'elit_code'
    ];
}
