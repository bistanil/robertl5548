<?php

namespace App\Http\Libraries\Import;

use App\Models\CatalogProduct;
use App\Models\CarModelType;
use App\Models\TypePart;
use DB;

Class PartTypeConnections{

	protected $appTypes;

	public function import()
	{
		$this->appTypes = CarModelType::pluck('td_id')->toArray();
		$parts = CatalogProduct::where('td_id', '>', 0)->get();
		foreach ($parts as $key => $part) {
			$this->typesWithDescriptionConnections($part);			
		}
	}

	
	public function typesWithDescriptionConnections($part)
	{
		$artId=$part->td_id;
		$types = DB::connection('tecdoc')->select(DB::raw("SELECT DISTINCT LAT_TYP_ID
															FROM LINK_LA_TYP
															INNER JOIN LINK_ART ON LINK_LA_TYP.LAT_LA_ID = LINK_ART.LA_ID
															WHERE LA_ART_ID=".$artId));
		$tdTypes=[];		
		foreach ($types as $key => $type) {
			$tdTypes[] = $type->LAT_TYP_ID;
		}		
		//$appTypes = array(1009023, 1009016, 12242, 9504, 23356, 28659);
		$partTypes = array_intersect($tdTypes, $this->appTypes);		
		foreach ($partTypes as $partType) {
			$laIds = DB::connection('tecdoc')->select(DB::raw("SELECT LA_ID
															FROM LINK_ART
															INNER JOIN LINK_LA_TYP ON LINK_ART.LA_ID=LINK_LA_TYP.LAT_LA_ID
															WHERE LA_ART_ID=".$artId."
															AND LINK_LA_TYP.LAT_TYP_ID=".$partType));
			$ids = '';
			foreach ($laIds as $laId) {				
				if ($laId->LA_ID == last($laIds)->LA_ID) $ids .= $laId->LA_ID;
				else $ids .= $laId->LA_ID.', ';
			}
			$features = DB::connection('tecdoc')->select(DB::raw("SELECT LAC_VALUE, t1.TEX_TEXT AS CRITERIA_TEXT, t2.TEX_TEXT AS CRITERIA_SHORT_DESCRIPTION, t3.TEX_TEXT AS LAC_KV_TEXT
																FROM LA_CRITERIA
																INNER JOIN CRITERIA ON LAC_CRI_ID=CRI_ID
																INNER JOIN DESIGNATIONS AS d1 ON CRITERIA.CRI_DES_ID=d1.DES_ID AND d1.DES_LNG_ID=".config('tdimport.tdLanguage')."
																INNER JOIN DES_TEXTS AS t1 ON d1.DES_TEX_ID=t1.TEX_ID
																INNER JOIN DESIGNATIONS AS d2 ON CRITERIA.CRI_SHORT_DES_ID=d2.DES_ID AND d2.DES_LNG_ID=".config('tdimport.tdLanguage')."
																INNER JOIN DES_TEXTS AS t2 ON d2.DES_TEX_ID=t2.TEX_ID
																INNER JOIN DESIGNATIONS AS d3 ON LA_CRITERIA.LAC_KV_DES_ID=d3.DES_ID AND d3.DES_LNG_ID=".config('tdimport.tdLanguage')."
																INNER JOIN DES_TEXTS AS t3 ON d3.DES_TEX_ID=t3.TEX_ID
																WHERE LAC_LA_ID IN (".$ids.")"));
			$content = '';
			foreach ($features as $feature) {
				$content .= '<tr><th>'.$feature->CRITERIA_TEXT.'<th>';
				if ($feature->LAC_VALUE != NULL) $content .= '<td>'.$feature->LAC_VALUE.'</td></tr>';
				else $content .= '<td>'.$feature->LAC_KV_TEXT.'</td></tr>';
			}
			$typePart = new TypePart();
			$typePart->type_id = CarModelType::whereTd_id($partType)->first()->id;
			$typePart->part_id = $part->id;
			$typePart->content = $content;
			$typePart->save();			
		}
	}

}