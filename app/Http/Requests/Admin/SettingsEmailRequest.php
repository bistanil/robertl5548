<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class SettingsEmailRequest extends Request
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
            'language'=>'required',
            'admin_emails'=>'required',
            'default_email_label' => 'required',
            'default' => 'required',
            'active' => 'required|max:50',            
        ];
        } else 
        {
            //Create validation rules
            return [
            'language'=>'required',
            'admin_emails'=>'required',
            'default_email_label' => 'required',
            'default' => 'required',
            'active' => 'required|max:50',
        ];
        }
        
    }
}
