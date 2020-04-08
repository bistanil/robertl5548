<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class GenerateFeedRequest extends Request
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
           
            ];
        } else 
        {
            //Create validation rules
            return [
                'file_name'=>'required',
                'type'=>'required',
                'feed_id'=>'required',
                'feed_prices' => 'required',
                'max_price' => 'required_with:min_price',
                'min_price' => 'required_with:max_price',
            ];
        }
        
    }
}
