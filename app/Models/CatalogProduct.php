<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CatalogProduct extends Model
{
    //
    use \Rutorika\Sortable\BelongsToSortedManyTrait;

    protected $table='catalog_products';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'meta_title','meta_keywords','meta_description','slug', 'content', 'active', 'language', 'code', 'short_description', 'offer', 'first_page', 'manufacturer_id', 'stock', 'td_id', 'type'
    ];

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public function catalog()
    {
        return $this->belongsTo('App\Models\Catalog');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\ProductCategory', 'product_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo('App\Models\Manufacturer');
    }

    public function attributes()
    {
        return $this->hasMany('App\Models\ProductAttribute','product_id');
    }

    public function prices()
    {
        return $this->hasMany('App\Models\ProductPrice','product_id');
    }

    public function images()
    {
        return $this->hasMany('App\Models\ProductImage','product_id');
    }

    public function partsCategories()
    {
        return $this->belongsToMany('App\Models\PartsCategory', 'category_parts', 'part_id', 'category_id');
    }

    public function types()
    {
        return $this->belongsToMany('App\Models\CarModelType', 'type_parts', 'part_id', 'type_id');
    }

    public function models()
    {
        return $this->belongsToMany('App\Models\CarModel', 'model_parts', 'part_id', 'model_id');
    }

    public function originalCodes()
    {
        return $this->hasMany('App\Models\PartOriginalCode','part_id');
    }

    public function codes()
    {
        return $this->hasMany('App\Models\PartCode','part_id');
    }

    public function typeLinks()
    {
        return $this->hasMany('App\Models\TypePart', 'part_id');
    }

    public function modelLinks()
    {
        return $this->hasMany('App\Models\ModelPart', 'part_id');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\ProductReview', 'product_id');
    }

    public function otherInfo($TYP_ID,$ART_ID)
    {
        $ids=$this->LaIDs($TYP_ID, $ART_ID);
        return DB::select(DB::raw("SELECT t1.TEX_TEXT AS label, LAC_VALUE, t2.TEX_TEXT AS `value`
                                    FROM LA_CRITERIA
                                    INNER JOIN CRITERIA ON LAC_CRI_ID=CRI_ID
                                    LEFT JOIN DESIGNATIONS AS d1 ON CRITERIA.CRI_DES_ID=d1.DES_ID AND d1.DES_LNG_ID=21
                                    LEFT JOIN DES_TEXTS AS t1 ON d1.DES_TEX_ID=t1.TEX_ID
                                    LEFT JOIN DESIGNATIONS AS d2 ON LA_CRITERIA.LAC_KV_DES_ID=d2.DES_ID AND d2.DES_LNG_ID=21
                                    LEFT JOIN DES_TEXTS AS t2 ON d2.DES_TEX_ID=t2.TEX_ID
                                WHERE LAC_LA_ID IN (".$ids.")"));
    }
    
    public function LaIDs($TYP_ID,$ART_ID)
    {
        $laids=DB::select(DB::raw("SELECT LA_ID
                            FROM LINK_ART
                            INNER JOIN LINK_LA_TYP ON LINK_ART.LA_ID=LINK_LA_TYP.LAT_LA_ID
                            WHERE LA_ART_ID=".$ART_ID."
                            AND LINK_LA_TYP.LAT_TYP_ID=".$TYP_ID));         
         $ids='0';
         foreach ($laids as $key => $laid) {
             if ($key==0) $ids.=',';
             if ($key<count($laids)-1) $ids.=$laid->LA_ID.',';
             else $ids.=$laid->LA_ID;
         }
         return $ids;
    }
}
