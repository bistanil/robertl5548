<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogListItem extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;

    protected $table='catalog_list_items';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'value', 'active', 'position'
    ];

    protected static $sortableGroupField = 'list_id';

    public function list()
    {
        return $this->belongsTo('App\Models\CatalogList', 'list_id');
    }
}