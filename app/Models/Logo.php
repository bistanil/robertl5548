<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{
    protected $table='settings_logos';

    protected $fillable = [
        'title', 'active', 'slogan', 'language', 'type'
    ];
}
