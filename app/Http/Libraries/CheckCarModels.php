<?php

namespace App\Http\Libraries;
use App;
use Auth;
use App\Models\CarModelType;


Class CheckCarModels {

	public function __construct()
	{
		
	}

	public function countModelCategoryTypes($category, $model)
	{
		return CarModelType::join('type_categories', function ($join) use ($category, $model) { 
                                            $join->where('type_categories.category_id', '=', $category->id)
                                            	 ->where('car_model_types.model_id', '=', $model->id)                                                 
                                                 ->on('type_categories.type_id', '=', 'car_model_types.id');
                                    })
								->select('car_model_types.*')
								->distinct()
								->where('car_model_types.active', '=', 'active')
								->orderBy('position')
								->count();
		
	}

}