<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwbContactPerson extends Model
{
    protected $fillable = [
        'active','email','name','phone'
    ];
}
