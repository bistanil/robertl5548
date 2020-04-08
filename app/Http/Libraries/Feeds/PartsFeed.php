<?php

namespace App\Http\Libraries\Feeds;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Models\Manufacturer;
use App\Models\TypePart;
use DB;


Class PartsFeed{

	protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function getTotal()
	{
		//dd($this->buildQuery()->toSql());
		return $this->buildQuery()->get()->count();
	}

	public function getProducts($limit, $offset)
	{
		//dd($this->buildQuery()->toSql());
		return $this->buildQuery()->limit($limit)->offset($offset)->get();		
	}

	public function buildQuery()
	{
		$request = $this->request;

		$query = CatalogProduct::join('model_parts', function($join) use ($request){
									$join->on('catalog_products.id', '=', 'model_parts.part_id');
							     })
							   ->join('manufacturers', function($join) use ($request){
							   		$join->on('catalog_products.manufacturer_id', '=', 'manufacturers.id');
							     })
							   ->join('car_models', function($join) use ($request){
							   		$join->on('model_parts.model_id', '=', 'car_models.id')
							   			 ->where('car_models.active', '=', 'active');
							   	 })
							   ->join('car_model_groups', function($join) use ($request){
							   		$join->on('car_model_groups.id', '=', 'car_models.model_group_id');
							   	 })
							   ->join('cars', function($join) use ($request){
							   		$join->on('cars.id', '=', 'car_model_groups.car_id');
							   	 });
		if ($request['feed_prices'] == 'withPrices')					   
		   $query = $query->join('catalog_price_product', function($join) use ($request){
					   		$join->on('catalog_products.id', '=', 'catalog_price_product.product_id')
					   			 ->whereRaw('catalog_price_product.price = (select min(price) from catalog_price_product where catalog_price_product.product_id = catalog_products.id)');
					   	 });
		$models = array_filter((array) json_decode($request['models']));		
		$cars = array_filter((array) json_decode($request['cars']));
		$manufacturers = array_filter((array) json_decode($request['manufacturer_id']));
		$query = $query->where('catalog_products.active', '=', 'active');
		$query = $query->where('car_models.active', '=', 'active');
		if (count($models) > 0) $query = $query->whereIn('car_models.id', $models);
		if (count($cars) > 0) $query = $query->whereIn('cars.id', $cars);
		if (count($manufacturers) > 0) $query = $query->whereIn('manufacturers.id', $manufacturers);
		if (count($manufacturers) > 0) $query = $query->whereRaw('manufacturers.id in ('.implode(',', $manufacturers).')');
		if ($request['feed_prices'] == 'withPrices')
		{
			if ($request['min_price'] > 0) $query = $query->where('price', '>=', $request['min_price']);
			else $query = $query->where('price', '>', 0);
			if ($request['max_price'] > 0) $query = $query->where('price', '<=', $request['max_price']);					
		} 
		$query = $query->select('model_parts.id as part_id', 'catalog_products.active', 'catalog_products.id as product_id', 'catalog_products.catalog_id as catalog_id', 'manufacturers.title as manufacturer_title', 'catalog_products.meta_title as product_meta_title', 'catalog_products.meta_keywords as meta_keywords', 'catalog_products.meta_description', 'catalog_products.manufacturer_id as manufacturer_id', 'catalog_products.title as part_title', 'catalog_products.slug', 'catalog_products.code', 'cars.title as car_title', 'catalog_products.stock','car_models.id as model_id', 'car_models.title as model_title', 'car_models.meta_title', 'car_models.construction_end_month', 'car_models.construction_end_year', 'car_models.construction_start_month', 'car_models.construction_start_year',  DB::raw("REPLACE(REPLACE(catalog_products.content, CHAR(13), ''), CHAR(10), '') as `content`"))->distinct();
		//dd($query->toSql());
		//$query = $query->orderBy('catalog_products.id');
		return $query;		
	}
}