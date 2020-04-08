<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    //
	protected static $sortableGroupField = 'category_id';

    protected $table='post_categories';

    protected $fillable = [
        'category_id', 'post_id', 'position'
    ];

    public function category()
    {
    	return $this->belongsTo('App\Models\NewsCategory','category_id', 'id');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\NewsPost','id','post_id');
    }
}
