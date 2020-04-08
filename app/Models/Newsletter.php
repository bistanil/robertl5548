<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'news_email', 'active', 'news_name'
    ];
}