<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class AwbWeightRequest extends Request
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
            'weight'=>'required',
            'width'=>'required',
            'height'=>'required', 
            'lenght'=>'required',           
        ];
        } else 
        {
            //Create validation rules
            return [
            'weight'=>'required',
            'width'=>'required',
            'height'=>'required', 
            'lenght'=>'required', 
        ];
        }
        
    }
}
