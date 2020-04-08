<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    //
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

    public function categories()
    {        
        return $this->hasMany('App\Models\CatalogCategory');
    }

    public function lists()
    {        
        return $this->hasMany('App\Models\CatalogList');
    }

    public function attributes()
    {        
        return $this->hasMany('App\Models\CatalogAttribute');
    }

    public function products()
    {        
        return $this->hasMany('App\Models\CatalogProduct');
    }

}
