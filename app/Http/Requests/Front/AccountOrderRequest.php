<?php

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;

class AccountOrderRequest extends Request
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
                'client_address_id' => 'required_if:require_delivery_address,1',
                'contact_person_name' => 'required_if:client_address_id,newAddress',
                'contact_person_phone' => 'required_if:client_address_id,newAddress',
                'county_id' => 'sometimes|required_if:client_address_id,newAddress',
                'city_id' => 'sometimes|required_if:client_address_id,newAddress',
                'client_delivery_postal_code' => '',
                'delivery_address' => 'required_if:client_address_id,newAddress',
                
                'client_company_id' => 'required_if:invoiceTo,company',
                'company_title' => 'required_if:client_company_id,newCompany&&required_if,invoiceTo,company',
                'fiscal_code' => 'required_if:client_company_id,newCompany&&required_if,invoiceTo,company',                
                'company_address' => 'required_if:client_company_id,newCompany&&required_if,invoiceTo,company',

                'transport_type' => 'required',
                'payment_method' => 'required',
                'accept_terms' => 'required',
                'accept_policy' => 'required'
            ];
    }
}
