<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class SubpageRequest extends Request
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
        return [
            //Update validation rules
            'active'=>'required',
            'title'=>'required|max:100|unique:pages,title,'.$this->get('id'),                
        ];       
    }
}
