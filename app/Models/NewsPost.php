<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'meta_title','meta_keywords','meta_description','slug', 'content', 'active', 'language', 'position'
    ];

     protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value);
    }

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function categories()
    {
        return $this->hasMany('App\Models\NewsCategory', 'category_id');
    }

    public function images()
    {
        return $this->hasMany('App\Models\PostImage', 'post_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\PostComment', 'post_id');
    }

    public function postCategories()
    {
        return $this->hasMany('App\Models\PostCategory', 'post_id');
    }
}
