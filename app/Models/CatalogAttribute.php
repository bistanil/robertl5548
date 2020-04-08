<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogAttribute extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;

    protected $table='catalog_attributes';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'active', 'language', 'is_list', 'list_id', 'is_filter', 'position'
    ];

    protected static $sortableGroupField = 'catalog_id';

    public function catalog()
    {
        return $this->belongsTo('App\Models\Catalog');
    }

    public function attributeList()
    {
        return $this->hasOne('App\Models\CatalogList', 'id','list_id');
    }

    public function activeListItems()
    {
        return $this->hasMany('App\Models\CatalogListItem','list_id', 'list_id')->where('active','=','active')->sorted();
    }

    public function kbValuesList()
    {
        return $this->hasMany('App\Models\ProductAttribute', 'attribute_id')->select('value')->distinct()->orderBy('value');
    }
}
