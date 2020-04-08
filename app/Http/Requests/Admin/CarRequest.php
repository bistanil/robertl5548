<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class CarRequest extends Request
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
                //Update validation rules
                'active'=>'required',
                'language'=>'required',
                'title'=>'required|max:100',                
            ];
        } else 
        {
            return [
                //Create validation rules
                'active'=>'required',
                'language'=>'required',
                'title'=>'required|max:100',
            ];
        }
    }
}
