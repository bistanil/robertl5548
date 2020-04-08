<?php

namespace App\Http\Libraries\Import;

use App\Models\CatalogProduct;
use App\Models\CarModelType;
use App\Models\Manufacturer;
use App\Models\TypePart;
use App\Models\PartsCategory;
use App\Models\CategoryPart;
use App\Models\PartOriginalCode;
use App\Models\ProductImage;
use DB;
use Storage;
use File;
use Image;

Class Parts{

	protected $appTypes;

	public function import()
	{
		$this->appTypes = CarModelType::pluck('td_id')->toArray();
		$products = $this->productsList();
		foreach ($products as $key => $product) {
			$manufacturer = $this->manufacturer($product->ART_SUP_ID);
			$part = new CatalogProduct();
			if ($part->bySlug(str_slug($this->productTitle($product->ART_ID).'-'.$manufacturer->title.'-'.$product->ART_ARTICLE_NR, "-"))==FALSE)
			{				
				$productTitle = $this->productTitle($product->ART_ID);
				$part->title = $productTitle;
				$part->meta_title = $productTitle;
				$part->meta_keywords = $productTitle;
				$part->meta_description = $productTitle;				
				$part->manufacturer_id = $manufacturer->id;
				$part->slug = str_slug($part->title.'-'.$manufacturer->title.'-'.$product->ART_ARTICLE_NR, "-");
				$part->code = $product->ART_ARTICLE_NR;
				$part->td_id = $product->ART_ID;
				$part->active = 'active';
				$part->language = config('tdimport.language');
				$part->first_page = 'no';
				$part->offer = 'no';
				$part->stock = 'in_stock';
				$part->content = $this->productContent($product->ART_ID);
				$part->catalog_id = 0;
				$part->save();
				$this->typesWithDescriptionConnections($part);
				$this->categoryConnections($part);
				$this->originalCodes($part);
				$this->images($part);
			}
		}
	}

	public function productsList()
	{
		$suppliers = DB::select(DB::raw("SELECT DISTINCT td_id FROM manufacturers"));
		$types = DB::select(DB::raw("SELECT DISTINCT td_id FROM car_model_types"));
		$suppliersString = '';
		foreach ($suppliers as $key => $supplier) {
			$suppliersString.=$supplier->td_id.',';
		}
		$suppliersString = rtrim($suppliersString, ",");
		$typesString = '';
		foreach ($types as $key => $type) {
			 $typesString.=$type->td_id.',';	
		}
		$typesString = rtrim($typesString, ",");
		return DB::connection('tecdoc')->select(DB::raw("SELECT DISTINCT ART_ID,ART_ARTICLE_NR, ART_SUP_ID
														FROM ARTICLES
														INNER JOIN LINK_ART ON ARTICLES.ART_ID=LINK_ART.LA_ART_ID
														INNER JOIN LINK_LA_TYP ON LINK_ART.LA_ID=LINK_LA_TYP.LAT_LA_ID
														WHERE LAT_TYP_ID IN (".$typesString.")
														AND LAT_SUP_ID IN (".$suppliersString.")
														ORDER BY ART_ARTICLE_NR"));
	}

	public function suppliersList()
	{
		return Manufacturer::select('td_id')->where('td_id', '>', 0)->get();
	}

	public function typesList()
	{
		return CarModelType::select('td_id')->get();
	}

	public function productTitle($artId)
	{
		$overviews = DB::connection('tecdoc')->select(DB::raw("SELECT
							                                    ART_ARTICLE_NR,
							                                    SUP_BRAND,
							                                    ART_SUP_ID,
							                                    ART_ID,
							                                    DES_TEXTS.TEX_TEXT AS ART_COMPLETE_DES_TEXT,
							                                    DES_TEXTS2.TEX_TEXT AS ART_DES_TEXT,
							                                    DES_TEXTS3.TEX_TEXT AS ART_STATUS_TEXT
							                                FROM
							                                               ARTICLES
							                                    INNER JOIN DESIGNATIONS ON DESIGNATIONS.DES_ID = ART_COMPLETE_DES_ID
							                                                           AND DESIGNATIONS.DES_LNG_ID = ".config('tdimport.tdLanguage')."
							                                    INNER JOIN DES_TEXTS ON DES_TEXTS.TEX_ID = DESIGNATIONS.DES_TEX_ID
							                                     LEFT JOIN DESIGNATIONS AS DESIGNATIONS2 ON DESIGNATIONS2.DES_ID = ART_DES_ID
							                                                                            AND DESIGNATIONS2.DES_LNG_ID = ".config('tdimport.tdLanguage')."
							                                     LEFT JOIN DES_TEXTS AS DES_TEXTS2 ON DES_TEXTS2.TEX_ID = DESIGNATIONS2.DES_TEX_ID
							                                    INNER JOIN SUPPLIERS ON SUP_ID = ART_SUP_ID
							                                    INNER JOIN ART_COUNTRY_SPECIFICS ON ACS_ART_ID = ART_ID
							                                    INNER JOIN DESIGNATIONS AS DESIGNATIONS3 ON DESIGNATIONS3.DES_ID = ACS_KV_STATUS_DES_ID
							                                                                            AND DESIGNATIONS3.DES_LNG_ID = ".config('tdimport.tdLanguage')."
							                                    INNER JOIN DES_TEXTS AS DES_TEXTS3 ON DES_TEXTS3.TEX_ID = DESIGNATIONS3.DES_TEX_ID
							                                WHERE
							                                    ART_ID = ".$artId."
							                                ;"));		
		foreach ($overviews as $key => $overview) {
			$productTitle=explode('; ',$overview->ART_COMPLETE_DES_TEXT);
			return $productTitle[0];	
		}		
	}

	public function manufacturer($supId)
	{
		$manufacturer = Manufacturer::whereTd_id($supId)->first();
		if (isset($manufacturer->id)) return $manufacturer;
		else return 0;
	}

	public function productContent($artId)
	{
		$features = DB::connection('tecdoc')->select(DB::raw("SELECT DISTINCT
								                                DES_TEXTS.TEX_TEXT AS CRITERIA_DES_TEXT,
								                                IFNULL (DES_TEXTS2.TEX_TEXT, ACR_VALUE) AS CRITERIA_VALUE_TEXT
								                                FROM
								                                ARTICLE_CRITERIA
								                                LEFT JOIN DESIGNATIONS AS DESIGNATIONS2 ON DESIGNATIONS2.DES_ID = ACR_KV_DES_ID
								                                LEFT JOIN DES_TEXTS AS DES_TEXTS2 ON DES_TEXTS2.TEX_ID = DESIGNATIONS2.DES_TEX_ID
								                                LEFT JOIN CRITERIA ON CRI_ID = ACR_CRI_ID
								                                LEFT JOIN DESIGNATIONS ON DESIGNATIONS.DES_ID = CRI_DES_ID
								                                LEFT JOIN DES_TEXTS ON DES_TEXTS.TEX_ID = DESIGNATIONS.DES_TEX_ID
								                                WHERE
								                                ACR_ART_ID = ".$artId." AND
								                                (DESIGNATIONS.DES_LNG_ID IS NULL OR DESIGNATIONS.DES_LNG_ID = ".config('tdimport.tdLanguage').") AND
								                                (DESIGNATIONS2.DES_LNG_ID IS NULL OR DESIGNATIONS2.DES_LNG_ID = ".config('tdimport.tdLanguage').")
								                                ;"));
		$content = '';
		foreach ($features as $key => $feature) {
			$content .= '<tr>
		                    <th>'.$feature->CRITERIA_DES_TEXT.'</th>
		                    <td>'.$feature->CRITERIA_VALUE_TEXT.'</td>
		                 </tr>';
		}		
		return $content;
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
			$features = DB::connection('tecdoc')->select(DB::raw("SELECT DISTINCT LAC_VALUE, t1.TEX_TEXT AS CRITERIA_TEXT, t2.TEX_TEXT AS CRITERIA_SHORT_DESCRIPTION, t3.TEX_TEXT AS LAC_KV_TEXT
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

	public function categoryConnections($part)
	{
		$categories = DB::connection('tecdoc')->select(DB::raw("SELECT DISTINCT LGS_STR_ID
																FROM LINK_GA_STR
																INNER JOIN LINK_ART ON LINK_GA_STR.LGS_GA_ID=LINK_ART.LA_GA_ID AND LINK_ART.LA_ART_ID=".$part->td_id."
																INNER JOIN SEARCH_TREE ON LINK_GA_STR.LGS_STR_ID = SEARCH_TREE.STR_ID AND SEARCH_TREE.STR_TYPE=1 AND SEARCH_TREE.STR_LEVEL>1"));
		foreach ($categories as $category) {
			$partCategory = PartsCategory::whereTd_id($category->LGS_STR_ID)->first();
			if ($partCategory != null)
			{
				$partCategoryLink = new CategoryPart();
				$partCategoryLink->category_id = $partCategory->id;
				$partCategoryLink->part_id = $part->id;
				$partCategoryLink->save();
			}
		}
	}

	public function originalCodes($part)
	{
		$codes = DB::connection('tecdoc')->select(DB::raw("SELECT
									ARL_KIND,
									IF (ART_LOOKUP.ARL_KIND = 2, SUPPLIERS.SUP_BRAND, BRANDS.BRA_BRAND) AS BRAND,
									ARL_DISPLAY_NR
								FROM
									           ART_LOOKUP
									 LEFT JOIN BRANDS ON BRA_ID = ARL_BRA_ID
									INNER JOIN ARTICLES ON ARTICLES.ART_ID = ART_LOOKUP.ARL_ART_ID
									INNER JOIN SUPPLIERS ON SUPPLIERS.SUP_ID = ARTICLES.ART_SUP_ID
								WHERE
									ARL_ART_ID = ".$part->td_id." AND
									ARL_KIND IN (3)
								ORDER BY
									ARL_KIND,
									BRA_BRAND,
									ARL_DISPLAY_NR"));

		foreach ($codes as $code) {
			$partCode = new PartOriginalCode();
			$partCode->part_id = $part->id;
			$partCode->brand = $code->BRAND;
			$partCode->code = $code->ARL_DISPLAY_NR;
			$partCode->save();
		}
	}

	public function images($part)
	{
		$images = DB::connection('tecdoc')->select(DB::raw("SELECT
                                CONCAT (
                                '',
                                GRA_TAB_NR, '/',
                                GRA_GRD_ID, '.',
                                IF (LOWER (DOC_EXTENSION) = 'jp2', 'jpg', LOWER (DOC_EXTENSION))
                                ) AS PATH
                                FROM
                                LINK_GRA_ART
                                INNER JOIN GRAPHICS ON GRA_ID = LGA_GRA_ID
                                INNER JOIN DOC_TYPES ON DOC_TYPE = GRA_DOC_TYPE
                                WHERE
                                LGA_ART_ID = ".$part->td_id." AND
                                (GRA_LNG_ID = ".config('tdimport.tdLanguage')." OR GRA_LNG_ID = 255) AND
                                GRA_DOC_TYPE NOT IN (1, 2)
                                ORDER BY
                                GRA_GRD_ID
                                ;"));
		foreach ($images as $key => $image) {
			if (File::exists('public/tdimages/'.$image->PATH) == TRUE)
            {
            	$partImage = new ProductImage();
            	$partImage->product_id = $part->id;
            	$partImage->active = 'active';
            	$partImage->title = $part->title.' '.$key;
            	$partImage->image = $partImage->product_id.time().str_replace('/','',$image->PATH);
            	try {
            		Storage::copy('public/tdimages/'.$image->PATH, config('hwimages.product.destination').$partImage->image);	
            	} catch (Exception $e) { }           	            	
            	$partImage->save();
            }
		}
	}

}