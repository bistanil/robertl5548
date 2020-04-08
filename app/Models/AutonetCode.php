<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutonetCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_id', 'manufacturer_id', 'code'
    ];

    public function product()
    {
        return $this->BelongsTo('App\Models\CatalogProduct', 'part_id', 'id');
    }    

}
