<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class AwbRequest extends Request
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
                'packages'=>'required',
                'dimension_id'=>'required',    
                'expedition_payment'=>'required', 
                'comments'=>'',       
        ];
        } else 
        {
            //Create validation rules
            return [
                'packages'=>'required',
                'dimension_id'=>'required',    
                'expedition_payment'=>'required', 
                'comments'=>'',
        ];
        }
        
    }
}
