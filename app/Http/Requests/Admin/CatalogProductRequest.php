<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class CatalogProductRequest extends Request
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
                'title'=>'required|max:300',                
                'code'=>'required|max:100',
                'categories'=>'required',
                //'currency_id' => 'required',
               
            ];
        } else 
        {
            return [
                //Create validation rules
                'active'=>'required',
                'title'=>'required|max:300',                
                'code'=>'required|max:100',
                'categories'=>'required',
                //'currency_id' => 'required',
            ];
        }
    }
}
