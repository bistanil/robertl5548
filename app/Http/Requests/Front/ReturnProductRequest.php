<?php

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;

class ReturnProductRequest extends Request
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
            'name'=>'required|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required',  
            'order_number' => 'required',
            'product_codes' => 'required',
            'return_back' => 'required', 
            'accept_terms' => 'required',
            'accept_policy' => 'required',
            'accept_return' => 'required'              

        ];
        } else 
        {
            //Create validation rules
            return [
            'name'=>'required|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required',
            'order_number' => 'required',
            'product_codes' => 'required',
            'return_back' => 'required', 
            'accept_terms' => 'required',
            'accept_policy' => 'required',
            'accept_return' => 'required' 
        ];
        }
        
    }
}
