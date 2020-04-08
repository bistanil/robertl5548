<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watermark extends Model
{

	protected $table='settings_watermark';

    protected $fillable = [
        'type'
    ];

}
