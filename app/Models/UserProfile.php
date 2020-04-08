<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content'
    ];

    public function users()
    {
        return $this->hasMany('App\Models\Users');
    }

    public function sections()
    {
        return $this->hasMany('App\Models\UserProfileSection', 'profile_id');
    }
}
