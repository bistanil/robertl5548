<?php

namespace App\Http\Libraries\Import;

use App\Models\CarModelGroup;
use App\Models\CarModel;
use DB;
use App\Http\Libraries\Import\ModelTypes;

Class Models{

	public function import(CarModelGroup $modelsGroup)
	{
		$models = $this->getItems($modelsGroup);
        foreach ($models as $key => $model) {
			$carModel = new CarModel();
			$carModel->language = $modelsGroup->language;
			$carModel->active = 'active';
			$carModel->model_group_id = $modelsGroup->id;
			$carModel->title = $model->MOD_CDS_TEXT;
            $carModel->slug = str_slug($modelsGroup->car->title.'-'.$model->MOD_CDS_TEXT, "-");
            $carModel->construction_start_year = substr($model->MOD_PCON_START, 0, 4);
            $carModel->construction_start_month = substr($model->MOD_PCON_START, 4, 6);
            if ($model->MOD_PCON_END != '') {
                $carModel->construction_end_year = substr($model->MOD_PCON_END, 0, 4);
                $carModel->construction_end_month = substr($model->MOD_PCON_END, 4, 6);
            } else {
                $carModel->construction_end_year = 2016;
                $carModel->construction_end_month = 12;
            }
            $carModel->meta_title = $modelsGroup->car->title.' '.$model->MOD_CDS_TEXT;
            $carModel->meta_keywords = $modelsGroup->car->title.' '.$model->MOD_CDS_TEXT;
            $carModel->meta_description = $modelsGroup->car->title.' '.$model->MOD_CDS_TEXT;
            $carModel->td_id = $model->MOD_ID;
            $carModel->save();
            $types = new ModelTypes();
            $types->import($carModel);
		}
	}

	public function getItems($modelsGroup)
	{
		return $models = DB::connection('tecdoc')->select(DB::raw("SELECT 
                                 MOD_ID, 
                                 MOD_MFA_ID, 
                                 TEX_TEXT AS MOD_CDS_TEXT, 
                                 MOD_PCON_START, 
                                 MOD_PCON_END
                                 FROM 
                                 MODELS 
                                 INNER JOIN COUNTRY_DESIGNATIONS ON CDS_ID = MOD_CDS_ID 
                                 INNER JOIN DES_TEXTS ON TEX_ID = CDS_TEX_ID 
                                 WHERE 
                                 MOD_MFA_ID = :td_id AND 
                                 CDS_LNG_ID = :language AND
                                 SUBSTRING_INDEX(TEX_TEXT, ' ', 1) = :title 
                                 ORDER BY 
                                 MOD_CDS_TEXT"), array(
									   'td_id' => $modelsGroup->car->td_id,
									   'language' => config('tdimport.tdLanguage'),
                                       'title' => $modelsGroup->title
									 ));
	}

}