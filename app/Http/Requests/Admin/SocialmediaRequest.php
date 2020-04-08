<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class SocialmediaRequest extends Request
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
            'type' => 'required|max:100|unique:settings_socialmedias,type,'.$this->get('id'),
            'link' => 'required|max:300',
            'active' => 'required|max:50',
        ];
        } else 
        {
            //Create validation rules
            return [
            'type'=>'required|max:100|unique:settings_socialmedias',
            'link' => 'required|max:300',
            'active' => 'required|max:50',
        ];
        }
        
    }
}
