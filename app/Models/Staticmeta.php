<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staticmeta extends Model
{
    //
    protected $table='settings_staticmetas';

    protected $fillable = [
        'page','language','meta_title','meta_keywords','meta_description'
    ];
}
