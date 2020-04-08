<?php

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;

class ClientRequest extends Request
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
                'email' => 'required|email|max:255|unique:clients,email,'.$this->get('id'),
                'phone' => 'required|numeric|unique:clients,phone,'.$this->get('id'),
                'password' => 'confirmed|min:6', 
                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5',
                'accept_terms' => 'required',
                'accept_policy' => 'required'              
            ];
        } else 
        {
            return [
                //
                'name' => 'required|max:100',
                'email' => 'required|email|max:255',
                'phone' => 'required|numeric',
                'password' => 'required|confirmed|min:6',
                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5',
                'accept_terms' => 'required',
                'accept_policy' => 'required' 
            ];
        }        
    }
}
