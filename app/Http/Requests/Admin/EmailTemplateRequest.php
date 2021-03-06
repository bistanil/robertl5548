<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class EmailTemplateRequest extends Request
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
            'type'=>'required',
            'title' => 'required|max:100|unique:email_templates,title,'.$this->get('id'),            
        ];
        } else 
        {
            //Create validation rules
            return [
            'language'=>'required',
            'type'=>'required',
            'title' => 'required|max:100|unique:email_templates',
        ];
        }
        
    }
}
