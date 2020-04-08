<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socialmedia extends Model
{
    //
    protected $table='settings_socialmedias';

    protected $fillable = [
        'type','link', 'active'
    ];
}
