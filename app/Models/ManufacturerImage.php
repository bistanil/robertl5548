<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufacturerImage extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;

    protected static $sortableGroupField = 'manufacturer_id';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'active', 'position'
    ];

    public function manufacturer()
    {
    	return $this->belongsTo('App\Models\Manufacturer');
    }
}
