<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class UserRequest extends Request
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
                'name' => 'required|max:100|unique:users,name,'.$this->get('id'),
                'email' => 'required|email|max:255|unique:users,email,'.$this->get('id'),
                'password' => 'confirmed|min:6',
                'profile_id' => 'required',
            ];
        } else 
        {
            return [
                //
                'name' => 'required|max:100|unique:users',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|confirmed|min:6',
                'profile_id' => 'required',
            ];
        }        
    }
}
