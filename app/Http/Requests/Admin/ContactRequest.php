<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class ContactRequest extends Request
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
            'language' => 'required|max:50',
            'active' => 'required|max:50',
            'phone1' => 'required',
            'phone2' => 'max:100',
            'phone3' => 'max:100',
            'email1' => 'required',
            'email2' => 'max:100',
            'email3' => 'max:100',
            'map' => '',
            'address' => '',
            'content' => '',
        ];
        } else 
        {
            //Create validation rules
            return [
            'language' => 'required|max:50',
            'active' => 'required|max:50',
            'phone1' => 'required',
            'phone2' => 'max:100',
            'phone3' => 'max:100',
            'email1' => 'required',
            'email2' => 'max:100',
            'email3' => 'max:100',
            'map' => '',
            'address' => '',
            'content' => '',
        ];
        }
        
    }
}
