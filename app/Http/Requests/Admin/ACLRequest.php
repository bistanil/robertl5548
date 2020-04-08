<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class ACLRequest extends Request
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
                'label' => 'required', 
                'group' => 'required',
                'show_actions' => 'required',              
            ];
        } else 
        {
            return [
                //Create validation rules
                'label'=>'required',                
                'group' => 'required',
                'show_actions' => 'required',
            ];
        }
    }
}
