<?php

namespace App\Http\Libraries\Import;

use App\Models\CarModel;
use App\Models\CarModelType;
use App\Http\Libraries\Import\TypeEngines;
use DB;

Class ModelTypes{

	public function import(CarModel $model)
	{
        $types = $this->getItems($model);
        foreach ($types as $key => $type) {
			$carType = new CarModelType();
			$carType->language = $model->language;
			$carType->active = 'active';
			$carType->model_id = $model->id;
			$carType->title =  $type->TYP_CDS_TEXT;
            if (is_null($carType->title)) $carType->title=' ';
            $carType->slug = str_slug($model->modelsGroup->car->title.'-'.$model->title.'-'.$type->TYP_CDS_TEXT.'-'.$type->TYP_KW_FROM, "-");
            $carType->construction_start_year = substr($type->TYP_PCON_START, 0, 4);
            $carType->construction_start_month = substr($type->TYP_PCON_START, 4, 6);
            if ($type->TYP_PCON_END != '') {
                $carType->construction_end_year = substr($type->TYP_PCON_END, 0, 4);
                $carType->construction_end_month = substr($type->TYP_PCON_END, 4, 6);
            } else {
                $carType->construction_end_year = 2016;
                $carType->construction_end_month = 12;
            }
            $carType->meta_title = $model->modelsGroup->car->title.' '.$model->title.' '.$type->TYP_CDS_TEXT;
            $carType->meta_keywords = $model->modelsGroup->car->title.' '.$model->title.' '.$type->TYP_CDS_TEXT;
            $carType->meta_description = $model->modelsGroup->car->title.' '.$model->title.' '.$type->TYP_CDS_TEXT;
            $carType->td_id = $type->TYP_ID;
            $carType->engine = $type->TYP_ENGINE_DES_TEXT;            
            $carType->hp = $type->TYP_HP_FROM;
            $carType->kw = $type->TYP_KW_FROM;
            $carType->cc = $type->TYP_CCM;
            $carType->cylinders = $type->TYP_CYLINDERS;
            $carType->fuel = $type->TYP_FUEL_DES_TEXT;
            $carType->body = $type->TYP_BODY_DES_TEXT;
            $carType->axle = $type->TYP_AXLE_DES_TEXT;
            $carType->max_weight = $type->TYP_MAX_WEIGHT;
            $carType->save();
            $engines = new TypeEngines();
            $engines->import($carType);
		}
	}

	public function getItems($model)
	{
		return $types = DB::connection('tecdoc')->select(DB::raw("SELECT DISTINCT
                                TYP_ID,
                                MFA_BRAND,
                                DES_TEXTS7.TEX_TEXT AS MOD_CDS_TEXT,
                                DES_TEXTS.TEX_TEXT AS TYP_CDS_TEXT,
                                TYP_PCON_START,
                                TYP_PCON_END,
                                TYP_CCM,
                                TYP_KW_FROM,
                                TYP_KW_UPTO,
                                TYP_HP_FROM,
                                TYP_HP_UPTO,
                                TYP_CYLINDERS,
                                DES_TEXTS2.TEX_TEXT AS TYP_ENGINE_DES_TEXT,
                                DES_TEXTS3.TEX_TEXT AS TYP_FUEL_DES_TEXT,
                                IFNULL (DES_TEXTS4.TEX_TEXT, DES_TEXTS5.TEX_TEXT) AS TYP_BODY_DES_TEXT,
                                DES_TEXTS6.TEX_TEXT AS TYP_AXLE_DES_TEXT,
                                TYP_MAX_WEIGHT
                                FROM
                                TYPES
                                INNER JOIN MODELS ON MOD_ID = TYP_MOD_ID
                                INNER JOIN MANUFACTURERS ON MFA_ID = MOD_MFA_ID
                                INNER JOIN COUNTRY_DESIGNATIONS AS COUNTRY_DESIGNATIONS2 ON COUNTRY_DESIGNATIONS2.CDS_ID = MOD_CDS_ID AND COUNTRY_DESIGNATIONS2.CDS_LNG_ID = ".config('tdimport.tdLanguage')."
                                INNER JOIN DES_TEXTS AS DES_TEXTS7 ON DES_TEXTS7.TEX_ID = COUNTRY_DESIGNATIONS2.CDS_TEX_ID
                                INNER JOIN COUNTRY_DESIGNATIONS ON COUNTRY_DESIGNATIONS.CDS_ID = TYP_CDS_ID AND COUNTRY_DESIGNATIONS.CDS_LNG_ID = ".config('tdimport.tdLanguage')."
                                INNER JOIN DES_TEXTS ON DES_TEXTS.TEX_ID = COUNTRY_DESIGNATIONS.CDS_TEX_ID
                                LEFT JOIN DESIGNATIONS ON DESIGNATIONS.DES_ID = TYP_KV_ENGINE_DES_ID AND DESIGNATIONS.DES_LNG_ID = ".config('tdimport.tdLanguage')."
                                LEFT JOIN DES_TEXTS AS DES_TEXTS2 ON DES_TEXTS2.TEX_ID = DESIGNATIONS.DES_TEX_ID
                                LEFT JOIN DESIGNATIONS AS DESIGNATIONS2 ON DESIGNATIONS2.DES_ID = TYP_KV_FUEL_DES_ID AND DESIGNATIONS2.DES_LNG_ID = ".config('tdimport.tdLanguage')."
                                LEFT JOIN DES_TEXTS AS DES_TEXTS3 ON DES_TEXTS3.TEX_ID = DESIGNATIONS2.DES_TEX_ID
                                LEFT JOIN LINK_TYP_ENG ON LTE_TYP_ID = TYP_ID
                                LEFT JOIN DESIGNATIONS AS DESIGNATIONS3 ON DESIGNATIONS3.DES_ID = TYP_KV_BODY_DES_ID AND DESIGNATIONS3.DES_LNG_ID = ".config('tdimport.tdLanguage')."
                                LEFT JOIN DES_TEXTS AS DES_TEXTS4 ON DES_TEXTS4.TEX_ID = DESIGNATIONS3.DES_TEX_ID
                                LEFT JOIN DESIGNATIONS AS DESIGNATIONS4 ON DESIGNATIONS4.DES_ID = TYP_KV_MODEL_DES_ID AND DESIGNATIONS4.DES_LNG_ID = ".config('tdimport.tdLanguage')."
                                LEFT JOIN DES_TEXTS AS DES_TEXTS5 ON DES_TEXTS5.TEX_ID = DESIGNATIONS4.DES_TEX_ID
                                LEFT JOIN DESIGNATIONS AS DESIGNATIONS5 ON DESIGNATIONS5.DES_ID = TYP_KV_AXLE_DES_ID AND DESIGNATIONS5.DES_LNG_ID = ".config('tdimport.tdLanguage')."
                                LEFT JOIN DES_TEXTS AS DES_TEXTS6 ON DES_TEXTS6.TEX_ID = DESIGNATIONS5.DES_TEX_ID
                                WHERE
                                TYP_MOD_ID = ".$model->td_id."
                                ORDER BY
                                MOD_CDS_TEXT,
                                TYP_CDS_TEXT,
                                TYP_PCON_START,
                                TYP_CCM"));
	}

}