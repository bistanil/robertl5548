<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class CompanyRequest extends Request
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
                'title' => 'required|max:200',
                'vat_code' => 'required',
                'registration_code' => 'required',                
                'address' => 'required',
                'default' => 'required',   
                'vat_percentage' => 'required',             
            ];
        } else 
        {
            return [
                //
                'title' => 'required|max:200',
                'vat_code' => 'required',
                'registration_code' => 'required',                
                'address' => 'required',
                'default' => 'required',
                'vat_percentage' => 'required',
            ];
        }        
    }
}
