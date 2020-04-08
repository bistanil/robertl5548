<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class OrderRequest extends Request
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
        if ($this->method()=='PUT' || $this->method()=='PATCH')
        {
            return [
                //Update validation rules
                'client_id' => 'required',
                'company_id' => 'required',
                'client_address_id' => 'required_if:require_delivery_address,1',
                'transport_type' => 'required',
                'fiscal_code' => 'required_if:client_company_id,newCompany',
                'company_title' => 'required_if:client_company_id,newCompany',
                'company_address' => 'required_if:client_company_id,newCompany',
                'contact_person_name' => 'required_if:client_address_id,newAddress',
                'contact_person_phone' => 'required_if:client_address_id,newAddress',
                'delivery_address' => 'required_if:client_address_id,newAddress',
            ];
        } else 
        {
            return [
                //Create validation rules
                'client_id' => 'required',
                'company_id' => 'required',
                'client_address_id' => 'required_if:require_delivery_address,1',
                'transport_type' => 'required',
                'fiscal_code' => 'required_if:client_company_id,newCompany',
                'company_title' => 'required_if:client_company_id,newCompany',
                'company_address' => 'required_if:client_company_id,newCompany',
                'contact_person_name' => 'required_if:client_address_id,newAddress',
                'contact_person_phone' => 'required_if:client_address_id,newAddress',
                'delivery_address' => 'required_if:client_address_id,newAddress',
            ];
        }
    }
}
