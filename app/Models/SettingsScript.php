<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingsScript extends Model
{
    protected $fillable = [
        'active', 'type', 'title', 'content'
    ];
}
