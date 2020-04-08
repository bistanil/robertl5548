<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class StaticmetaRequest extends Request
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
            'page' => 'required|max:50',
            'language' => 'required|max:50',
            'meta_title' => 'max:250',
            'meta_keywords' => 'max:500',
            'meta_description' => 'max:1000',
        ];
        } else 
        {
            //Create validation rules
            return [
            'page' => 'required|max:50',
            'language' => 'required|max:50',
            'meta_title' => 'max:250',
            'meta_keywords' => 'max:500',
            'meta_description' => 'max:1000',
        ];
        }
        
    }
}
