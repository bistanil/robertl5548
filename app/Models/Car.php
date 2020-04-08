<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'td_id', 'title', 'meta_title','meta_keywords','meta_description','slug', 'content', 'active', 'language', 'position', 'first_page', 'type'
    ];

    protected static $sortableGroupField = 'language';

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function modelsGroups()
    {
        return $this->hasMany('App\Models\CarModelGroup');
    }

    public function models()
    {
        return $this->hasManyThrough('App\Models\CarModel', 'App\Models\CarModelGroup', 'car_id', 'model_group_id');
    }

}
