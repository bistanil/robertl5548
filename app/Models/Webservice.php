<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webservice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'username', 'key', 'has_list'
    ];
}
