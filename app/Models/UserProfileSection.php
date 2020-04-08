<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfileSection extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'profile_id', 'group', 'method', 'authorised'
    ];

    
}
