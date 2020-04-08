<?php

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;

class ProductReviewRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        session()->flash('reviewTabActive', true);
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
                'title' => 'required|max:200',
                'content' => 'required',
                'rating' => 'required',
                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5'                  
            ];
        } else 
        {
            return [
                //
                'title' => 'required|max:200',
                'content' => 'required',
                'rating' => 'required',
                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5'  
            ];
        }        
    }
}
