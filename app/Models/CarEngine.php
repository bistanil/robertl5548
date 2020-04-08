<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarEngine extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id', 'active', 'language', 'position', 'code', 'kw', 'hp', 'cylinders', 'ccm', 'litres', 'crankshaft', 'torque', 'extension', 'drilling', 'rpm', 'valves', 'design', 'fuel', 'fuel_supply', 'type', 'charge', 'transmission', 'cooling', 'cylinders_description', 'gas_norm'
    ];

    protected static $sortableGroupField = 'type_id';

    public function carType()
    {
    	return $this->belongsTo('App\Models\CarModelType', 'type_id', 'id');
    }

}
