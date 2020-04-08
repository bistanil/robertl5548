<?php

namespace App\Models\Elit;

use Illuminate\Database\Eloquent\Model;

class ElitSearchedCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_id', 'searched_at'
    ];
}
