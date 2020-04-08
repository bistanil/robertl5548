<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogList extends Model
{
    //
    protected $table='catalog_lists';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'slug', 'language'
    ];

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function items($listId)
    {
        return $this->find($listId)->hasMany('App\Models\CatalogListItem', 'list_id');
    }

    public function catalog()
    {
        return $this->belongsTo('App\Models\Catalog');
    }
}
