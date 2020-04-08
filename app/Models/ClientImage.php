<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientImage extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;

    protected static $sortableGroupField = 'client_id';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'active', 'position'
    ];

    public function client()
    {
    	return $this->belongsTo('App\Models\Client');
    }
}
