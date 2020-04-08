<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use \Rutorika\Sortable\SortableTrait;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'second_title', 'city', 'meta_title','meta_keywords','meta_description','slug', 'content', 'active', 'language', 'position'
    ];

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value);
    }

    public function messages()
    {
        return $this->hasMany('App\Models\Career', 'career_id');
    }

    public function candidates()
    {
        return $this->hasMany('App\Models\CareerApply', 'career_id');
    }
}
