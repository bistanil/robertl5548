<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Suggestion extends Model
{
    use Notifiable;

    protected $fillable = [
        'language','email','name','phone','type','content','status'
    ];
}
