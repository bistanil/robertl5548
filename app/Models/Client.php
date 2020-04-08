<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email','phone','gender','slug', 'password', 'active', 'origin' ,'remember_token', 'accept_terms', 'accept_policy'
    ];

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function deliveryAddresses()
    {
        return $this->hasMany('App\Models\DeliveryAddress');
    }

    public function companies()
    {
        return $this->hasMany('App\Models\ClientCompany');
    }

    public function notes()
    {
        return $this->hasMany('App\Models\ClientNote');
    }

    public function discounts()
    {
        return $this->hasMany('App\Models\Discount');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\ProductReview');
    }

    public function images()
    {
        return $this->hasMany('App\Models\ClientImage');
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\ClientInvoice');
    }

    public function routeNotificationFor()
    {
        return $this->email;
    }

    public function cars()
    {
        return $this->hasMany('App\Models\ClientCar');
    }

}
