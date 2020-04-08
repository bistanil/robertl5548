<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Order extends Model
{
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'language',  

        'company_id',   
        'company_title',
        'company_vat_code',
        'company_registration_code',
        'company_address',
        'company_bank',
        'company_bank_account',

        'client_id',        
        'client_name', 
        'client_email', 
        'client_phone', 
        'client_gender',

        'client_company_title', 
        'client_company_fiscal_code',
        'client_company_registration_number',
        'client_company_bank',
        'client_company_bank_account',
        'client_company_address',

        'client_delivery_address',
        'client_delivery_contact_person',
        'client_delivery_phone',
        'client_delivery_county',
        'client_delivery_city',
        'client_delivery_postal_code',

        'transport_cost',
        'transport_type',

        'car_info',
        'vin',

        'status',
        'intern_status',

        'payment_method',
        
        'currency',
        'discount_amount',
        'total',

        'observations',

        'accept_terms', 
        'accept_policy',
        
        'received_at',
        
    ];

    protected $dates = ['created_at', 'updated_at', 'received_at'];

    public function getReceivedDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function setReceivedDateAttribute($value)
    {
        $this->attributes['received_at'] = Carbon::parse($value);
    }

    public function client()
    {
    	return $this->belongsTo('App\Models\Client');
    }

    public function company()
    {
    	return $this->belongsTo('App\Models\Company');
    }

    public function items()
    {
        return $this->hasMany('App\Models\OrderItem');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'updated_by');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\TransportType', 'transport_type');
    }

    public function routeNotificationFor()
    {
        return $this->client_email;
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\OrderInvoice');
    }

    public function warranties()
    {
        return $this->hasMany('App\Models\Warranty');
    }

    public function awbDetail()
    {
        return $this->hasOne('App\Models\AwbDetail', 'order_id', 'id');
    }

    public function notes()
    {
        return $this->hasMany('App\Models\OrderNote');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'order_id');
    }

}
