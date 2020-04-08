<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'slug', 'type', 'more_info', 'language'
    ];

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }
}
