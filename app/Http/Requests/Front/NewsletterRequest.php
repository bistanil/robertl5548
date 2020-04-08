<?php

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;

class NewsletterRequest extends Request
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
                'news_email' => 'required|max:200|email|unique:newsletters',    
                'news_name' => '',  
                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5'          
            ];
        } else 
        {
            return [
                //
                'news_email' => 'required|max:200|email|unique:newsletters',
                'news_name' => '',
                'my_name'   => 'honeypot',
                'my_time'   => 'required|honeytime:5'
            ];
        }        
    }
}
