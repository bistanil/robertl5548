<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModelType extends Model
{
    //
     use \Rutorika\Sortable\SortableTrait;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model_id', 'title', 'active', 'language', 'position', 'construction_start_year', 'construction_start_month', 'construction_end_year', 'construction_end_month', 'meta_title', 'meta_keywords', 'meta_description', 'slug', 'cc', 'kw', 'hp', 'cylinders', 'engine', 'fuel', 'body', 'axle', 'max_weight', 'content', 'td_id'
    ];

    protected static $sortableGroupField = 'model_id';

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function model()
    {
    	return $this->belongsTo('App\Models\CarModel', 'model_id', 'id');
    }

    public function engines()
    {
        return $this->hasMany('App\Models\CarEngine', 'type_id');
    }
   
}