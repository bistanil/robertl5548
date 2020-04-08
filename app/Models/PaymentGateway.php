<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
	use \Rutorika\Sortable\SortableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'active', 'username', 'password', 'key', 'test', 'position', 'private_key', 'public_key'
    ];

}
