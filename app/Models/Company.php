<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'vat_code', 'registration_code', 'address', 'bank', 'bank_account', 'default','vat_percentage'
    ];
}
