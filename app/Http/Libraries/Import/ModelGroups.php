<?php

namespace App\Http\Libraries\Import;

use App\Models\Car;
use App\Models\CarModelGroup;
use DB;
use Storage;
use File;
use Image;
use App\Http\Libraries\Import\Models;


Class ModelGroups{

	public function import(Car $car)
	{
		$modelsGroups = $this->getItems($car);
		foreach ($modelsGroups as $key => $modelsGroup) {
			$group = new CarModelGroup();
			$group->language = $car->language;
			$group->active = 'active';
			$group->car_id = $car->id;
			$group->title = $modelsGroup->MOD_CDS_TEXT;
			$imageTitle = str_slug($car->title, "_").'_'.$modelsGroup->MOD_CDS_TEXT.'.jpg';
			if (File::exists('public/tdmodels/'.$imageTitle) == TRUE)
            {
                $group->image = time().$imageTitle;
                Storage::copy('public/tdmodels/'.$imageTitle, config('hwimages.carModelsGroup.destination').$group->image);
                $extension = File::extension(config('hwimages.carModelsGroup.destination').$group->image);
                if ($extension == 'jpg' || $extension == 'png' || $extension == 'gif')
                {
                    $img = Image::make(config('hwimages.carModelsGroup.destination').$group->image)->heighten(config('hwimages.carModelsGroup.height'), function ($constraint) {
                            $constraint->upsize();
                        });                
                    $img->save(config('hwimages.carModelsGroup.destination').$group->image);
                }
            }
            $group->save();
            $models = new Models();
            $models->import($group);
		}
	}

	public function getItems($car)
	{
        if ($car->title=='AUDI' || $car->title=='BMW' || $car->title=='MERCEDES-BENZ' || $car->title=='VW')
		return $modelsGroups = DB::connection('tecdoc')->select(DB::raw('SELECT DISTINCT 
                                 SUBSTRING_INDEX(TEX_TEXT," ", 1) AS MOD_CDS_TEXT 
                                 FROM 
                                 MODELS 
                                 INNER JOIN COUNTRY_DESIGNATIONS ON CDS_ID = MOD_CDS_ID 
                                 INNER JOIN DES_TEXTS ON TEX_ID = CDS_TEX_ID 
                                 WHERE 
                                 MOD_MFA_ID = :td_id AND 
                                 CDS_LNG_ID = :language AND
                                 MOD_PCON_START > 197000
                                 ORDER BY 
                                 MOD_CDS_TEXT'), array(
									   'td_id' => $car->td_id,
									   'language' => config('tdimport.tdLanguage')
									 ));
        return $modelsGroups = DB::connection('tecdoc')->select(DB::raw('SELECT DISTINCT 
                                 SUBSTRING_INDEX(TEX_TEXT," ", 1) AS MOD_CDS_TEXT 
                                 FROM 
                                 MODELS 
                                 INNER JOIN COUNTRY_DESIGNATIONS ON CDS_ID = MOD_CDS_ID 
                                 INNER JOIN DES_TEXTS ON TEX_ID = CDS_TEX_ID 
                                 WHERE 
                                 MOD_MFA_ID = :td_id AND 
                                 CDS_LNG_ID = :language AND
                                 MOD_PCON_START > 199000
                                 ORDER BY 
                                 MOD_CDS_TEXT'), array(
                                       'td_id' => $car->td_id,
                                       'language' => config('tdimport.tdLanguage')
                                     ));

	}

}