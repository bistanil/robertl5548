<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class DeliveryAddressRequest extends Request
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
                //
                'name' => 'required|max:100',
                'phone' => 'required',
                'address' => 'required',
                'county_id' => 'required',  
                'city_id' => 'required',
                'postal_code' => ''               
            ];
        } else 
        {
            return [
                //
                'name' => 'required|max:100',
                'phone' => 'required',
                'address' => 'required', 
                'county_id' => 'required',  
                'city_id' => 'required',
                'postal_code' => '' 
            ];
        }        
    }
}
