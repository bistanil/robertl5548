<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class ExcelWithSupplierImportRequest extends Request
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
            'excel'=>'required|mimes:xlsx',
            'supplier_id' => 'required'
        ];        
    }
}
