<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Awb extends Model
{
    protected $fillable = [
            'order_id',
            'service_type', 
            'bank', 
            'bank_account', 
            'envelopes', 
            'packages', 
            'weight',
            'expedition_payment',
            'cash_on_delivery',
            'cash_on_delivery_payment_at',
            'declared_value',
            'contact_person_sender',  
            'comments',
            'content',
            'recipient_name',
            'recipient_contact_person',
            'recipient_phone',
            'recipient_fax',
            'recipient_email',
            'recipient_county',
            'recipient_city',
            'recipient_street',
            'recipient_street_no',
            'recipient_postal_code',
            'recipient_block',
            'recipient_scale',
            'recipient_floor',
            'recipient_apartment',
            'dimension_id',
            'package_height',
            'package_width',
            'package_length',
            'restitution',
            'cost_center',
            'options',
            'packing',
            'personal_information',
    ];
}
