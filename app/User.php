<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_id', 'slug', 'visible'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function visible()
    {
        return $this->whereVisible('yes')->get();
    }

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function profile()
    {
        return $this->belongsTo('App\Models\UserProfile', 'profile_id');
    }

}
