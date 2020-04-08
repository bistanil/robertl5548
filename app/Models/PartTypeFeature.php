<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartTypeFeature extends Model
{
    use \Rutorika\Sortable\SortableTrait;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_id', 'type_id', 'label', 'value'
    ];

}
