<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutonetSearchedCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_id', 'searched_at'
    ];

    public function product()
    {
        return $this->BelongsTo('App\Models\CatalogProduct', 'id', 'part_id');
    }
}
