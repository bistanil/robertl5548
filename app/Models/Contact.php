<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    //
    protected $table='settings_contacts';

    protected $fillable = [
        'language','email1','email2','email3','phone1','phone2','phone3','address','map','map_link','content', 'active', 'schedule'
    ];
}
