<?php

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;

class NoAccountOrderRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
                //Update validation rules
                'account' => 'required',
                'invoiceTo' => 'required',
                'name' => 'required',
                'email' => 'required|email|max:255',
                'phone' => 'required',
                'password' => "required_if:account,newAccount|confirmed|min:6",

                'contact_person_name' => 'required_if:require_delivery_address,1',
                'contact_person_phone' => 'required_if:require_delivery_address,1',
                'delivery_address' => 'required_if:require_delivery_address,1',
                'county_id' => 'sometimes|required_if:require_delivery_address,1',
                'city_id' => 'sometimes|required_if:require_delivery_address,1',
                'client_delivery_postal_code' => '',

                'company_title' => 'required_if:invoiceTo,company',
                'fiscal_code' => 'required_if:invoiceTo,company',
                'registration_number' => '',
                'company_address' => 'required_if:invoiceTo,company',

                'transport_type' => 'required',
                'payment_method' => 'required',

                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5',
                'accept_terms' => 'required',
                'accept_policy' => 'required'
            ];
    }
}
