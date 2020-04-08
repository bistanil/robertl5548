<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ContactMessage extends Model
{
    //
    use Notifiable;

    protected $fillable = [
        'language','email','name','phone','subject','content','status', 'accept_terms', 'accept_policy','second_status'
    ];

}