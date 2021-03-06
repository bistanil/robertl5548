<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class LogoRequest extends Request
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
                'title'=>'max:100',
                'active' => 'required|max:50',
            ];
        } else 
        {
            //Create validation rules
            return [
            'title'=>'max:100',
            'active' => 'required|max:50',
            'image' => 'required'
        ];
        }
        
    }
}
