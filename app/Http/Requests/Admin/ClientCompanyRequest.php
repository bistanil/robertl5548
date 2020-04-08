<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class ClientCompanyRequest extends Request
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
                'fiscal_code' => 'required',
                'registration_number' => 'required',                
                'address' => 'required',                
            ];
        } else 
        {
            return [
                //
                'title' => 'required|max:200',
                'fiscal_code' => 'required',
                'registration_number' => 'required',                
                'address' => 'required',
            ];
        }        
    }
}
