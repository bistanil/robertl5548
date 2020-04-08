<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'status', 'title', 'content', 'rating', 'name', 'email', 'client_id'
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value);
    }

    public function product()
    {
    	return $this->belongsTo('App\Models\CatalogProduct');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}
