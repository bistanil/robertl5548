<?php

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;

class CareerApplyRequest extends Request
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
                'name' => 'required|max:200',
                'email' => 'required|email',
                'phone' => 'required',
                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5'                  
            ];
        } else 
        {
            return [
                //
                'name' => 'required|max:200',
                'email' => 'required|email',
                'phone' => 'required', 
                'docs' => 'required', 
                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5'                
            ];
        }        
    }
}
