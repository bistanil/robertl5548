<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModelGroup extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'car_id', 'title', 'active', 'language', 'position'
    ];

    protected static $sortableGroupField = 'car_id';

    public function car()
    {
    	return $this->belongsTo('App\Models\Car');
    }

    public function models()
    {
        return $this->hasMany('App\Models\CarModel', 'model_group_id');
    }

}
