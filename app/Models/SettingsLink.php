<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingsLink extends Model
{
    protected $fillable = [
        'active', 'language', 'title', 'link', 'content'
    ];
}
