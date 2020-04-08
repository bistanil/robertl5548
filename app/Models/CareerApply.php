<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CareerApply extends Model
{
     use Notifiable;

    protected $fillable = [
        'email','name','phone', 'status', 'career_id'
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

    public function career()
    {
    	return $this->belongsTo('App\Models\Career');
    }

}
