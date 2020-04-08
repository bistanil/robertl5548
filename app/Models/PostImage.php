<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;

    protected static $sortableGroupField = 'post_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'active', 'position'
    ];

    public function post()
    {
    	return $this->belongsTo('App\Models\NewsPost');
    }
}
