<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model

{
	protected $table='ltm_translations';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'key', 'value', 'group', 'status', 'locale'
    ];

}