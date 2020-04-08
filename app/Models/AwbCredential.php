<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwbCredential extends Model
{
    protected $fillable = [
        'type','username','client_id','user_pass'
    ];
}
