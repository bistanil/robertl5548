<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class AwbContactPersonRequest extends Request
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
            //Update validation rules
            return [
            'active' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
        ];
        } else 
        {
            //Create validation rules
            return [
            'active' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
        ];
        }
        
    }
}
