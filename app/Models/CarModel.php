<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model_group_id', 'title', 'active', 'language', 'position', 'construction_start_year', 'construction_start_month', 'construction_end_year', 'construction_end_month', 'meta_title', 'meta_keywords', 'meta_description', 'slug', 'content', 'td_id'
    ];

    protected static $sortableGroupField = 'model_group_id';

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function modelsGroup()
    {
    	return $this->belongsTo('App\Models\CarModelGroup', 'model_group_id', 'id');
    }

    public function types()
    {
        return $this->hasMany('App\Models\CarModelType', 'model_id');
    }
}
