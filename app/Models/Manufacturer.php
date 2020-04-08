<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
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

    public function images($slug)
    {
        return $this->bySlug($slug)->hasMany('App\Models\ManufacturerImage');
    }

    public function activeImages()
    {
        return $this->hasMany('App\Models\ManufacturerImage');
    }

    public function products()
    {
        return $this->hasMany('App\Models\CatalogProduct');
    }
}
