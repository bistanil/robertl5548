<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class CarSearchRequest extends Request
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
                'car_id' => 'required',
                'model_id' => 'required',
                'type_id' => 'required',                
            ];
        } else 
        {
            return [
                //
                'car_id' => 'required',
                'model_id' => 'required',
                'type_id' => 'required',
            ];
        }        
    }
}
