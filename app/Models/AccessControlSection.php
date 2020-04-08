<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessControlSection extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group', 'method', 'route_name', 'label', 'parent', 'show_actions'
    ];

}