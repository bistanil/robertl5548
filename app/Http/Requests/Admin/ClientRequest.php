<?php

namespace App\Http\Requests\Admin;

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
                'email' => 'required_if:phone,null|email|max:255|unique:clients,email,'.$this->get('id'),
                'phone' => 'required_if:email,null|unique:clients,phone,'.$this->get('id'),
                'password' => 'confirmed|min:6',           
            ];
        } else 
        {
            return [
                //
                'name' => 'required|max:100',
                'email' => 'required_if:phone,null|email|max:255|unique:clients',
                'phone' => 'required_if:email,null|unique:clients',
                'password' => 'required|confirmed|min:6',
            ];
        }        
    }
}
