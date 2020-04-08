<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
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

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function posts()
    {
        return $this->belongsToMany('App\Models\NewsPost', 'post_categories', 'category_id', 'post_id');
    }
}
