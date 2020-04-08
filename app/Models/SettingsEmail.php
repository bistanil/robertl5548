<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingsEmail extends Model
{
    //
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_emails', 'default_email_label', 'default', 'language', 'active'
    ];

    public function routeNotificationFor()
    {
    	return $this->admin_emails;
    }
}