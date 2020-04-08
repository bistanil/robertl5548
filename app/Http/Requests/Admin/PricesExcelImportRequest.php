<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class PricesExcelImportRequest extends Request
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
            //Validation rules
            'supplier_id'=>'required',
            'excel'=>'required|mimes:xlsx',
        ];        
    }
}
