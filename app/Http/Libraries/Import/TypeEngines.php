<?php

namespace App\Http\Libraries\Import;

use App\Models\CarModelType;
use App\Models\CarEngine;
use DB;

Class TypeEngines{

	public function import(CarModelType $type)
	{
        $engines = $this->getItems($type);
        foreach ($engines as $key => $engine) {
			$carEngine = new CarEngine();
			$carEngine->language = $type->language;
			$carEngine->active = 'active';
			$carEngine->type_id = $type->id;
			$carEngine->td_id = $engine->ENG_ID;
            $carEngine->code = $engine->ENG_CODE;            
            $carEngine->hp = $engine->ENG_HP_FROM;
            $carEngine->kw = $engine->ENG_KW_FROM;
            $carEngine->ccm = $engine->ENG_CCM_FROM;
            $carEngine->cylinders = $engine->ENG_CYLINDERS;
            $carEngine->cylinders_description = $engine->ENG_KV_CYLINDERS;
            $carEngine->valves = $engine->ENG_VALVES;
            $carEngine->litres = $engine->ENG_LITRES_FROM;
            $carEngine->crankshaft = $engine->ENG_CRANKSHAFT;
            $carEngine->torque = $engine->ENG_TORQUE_FROM;
            $carEngine->extension = $engine->ENG_EXTENSION;
            $carEngine->drilling = $engine->ENG_DRILLING;
            $carEngine->rpm = $engine->ENG_KW_RPM_FROM;
            $carEngine->design = $engine->ENG_KV_DESIGN;
            $carEngine->fuel = $engine->ENG_KV_FUEL_TYPE;
            $carEngine->fuel_supply = $engine->ENG_KV_FUEL_SUPPLY;
            $carEngine->type = $engine->ENG_KV_ENGINE;
            $carEngine->charge = $engine->ENG_KV_CHARGE;
            $carEngine->transmission = $engine->ENG_KV_CONTROL;
            $carEngine->cooling = $engine->ENG_KV_COOLING;
            $carEngine->gas_norm = $engine->ENG_KV_GAS_NORM;
            $carEngine->save();
		}
	}

	public function getItems($type)
	{
		return $engines = DB::connection('tecdoc')->select(DB::raw("SELECT ENG_CODE,
                                                                         ENG_ID,
                                                                         ENG_KW_FROM,
                                                                         ENG_HP_FROM,
                                                                         ENG_VALVES,
                                                                         ENG_CYLINDERS,
                                                                         ENG_CCM_FROM,
                                                                         ENG_LITRES_FROM,
                                                                         ENG_CRANKSHAFT,
                                                                         ENG_EXTENSION,
                                                                         ENG_DRILLING,
                                                                         ENG_KW_RPM_FROM,
                                                                         ENG_TORQUE_FROM,
                                                                         t1.TEX_TEXT AS ENG_KV_DESIGN,
                                                                         t2.TEX_TEXT AS ENG_KV_FUEL_TYPE,
                                                                         t3.TEX_TEXT AS ENG_KV_FUEL_SUPPLY,
                                                                         t4.TEX_TEXT AS ENG_KV_ENGINE,
                                                                         t5.TEX_TEXT AS ENG_KV_CHARGE,
                                                                         t6.TEX_TEXT AS ENG_KV_CONTROL,
                                                                         t7.TEX_TEXT AS ENG_KV_COOLING,
                                                                         t8.TEX_TEXT AS ENG_KV_CYLINDERS,
                                                                         t9.TEX_TEXT AS ENG_KV_GAS_NORM
                                                            FROM `ENGINES`
                                                            LEFT JOIN DESIGNATIONS AS d1 ON `ENGINES`.ENG_KV_DESIGN_DES_ID=d1.DES_ID AND d1.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t1 ON d1.DES_TEX_ID=t1.TEX_ID
                                                            LEFT JOIN DESIGNATIONS AS d2 ON `ENGINES`.ENG_KV_FUEL_TYPE_DES_ID=d2.DES_ID AND d2.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t2 ON d2.DES_TEX_ID=t2.TEX_ID
                                                            LEFT JOIN DESIGNATIONS AS d3 ON `ENGINES`.ENG_KV_FUEL_SUPPLY_DES_ID=d3.DES_ID AND d3.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t3 ON d3.DES_TEX_ID=t3.TEX_ID
                                                            LEFT JOIN DESIGNATIONS AS d4 ON `ENGINES`.ENG_KV_ENGINE_DES_ID=d4.DES_ID AND d4.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t4 ON d4.DES_TEX_ID=t4.TEX_ID
                                                            LEFT JOIN DESIGNATIONS AS d5 ON `ENGINES`.ENG_KV_CHARGE_DES_ID=d5.DES_ID AND d5.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t5 ON d5.DES_TEX_ID=t5.TEX_ID
                                                            LEFT JOIN DESIGNATIONS AS d6 ON `ENGINES`.ENG_KV_CONTROL_DES_ID=d6.DES_ID AND d6.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t6 ON d6.DES_TEX_ID=t6.TEX_ID
                                                            LEFT JOIN DESIGNATIONS AS d7 ON `ENGINES`.ENG_KV_COOLING_DES_ID=d7.DES_ID AND d7.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t7 ON d7.DES_TEX_ID=t7.TEX_ID
                                                            LEFT JOIN DESIGNATIONS AS d8 ON `ENGINES`.ENG_KV_CYLINDERS_DES_ID=d8.DES_ID AND d8.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t8 ON d8.DES_TEX_ID=t8.TEX_ID
                                                            LEFT JOIN DESIGNATIONS AS d9 ON `ENGINES`.ENG_KV_GAS_NORM_DES_ID=d9.DES_ID AND d9.DES_LNG_ID=".config('tdimport.tdLanguage')."
                                                            LEFT JOIN DES_TEXTS AS t9 ON d9.DES_TEX_ID=t9.TEX_ID
                                                            LEFT JOIN LINK_TYP_ENG ON LINK_TYP_ENG.LTE_ENG_ID=`ENGINES`.ENG_ID
                                                            WHERE LTE_TYP_ID =".$type->td_id));
	}

}