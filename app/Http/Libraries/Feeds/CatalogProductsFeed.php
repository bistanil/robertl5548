<?php

namespace App\Http\Libraries\Feeds;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\Catalog;
use DB;


Class CatalogProductsFeed{

	protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function getTotal()
	{
		return $this->buildQuery()->get()->count();
	}

	public function getProducts($limit, $offset)
	{
		return $this->buildQuery()->orderBy('catalog_products.id')->limit($limit)->offset($offset)->get();
	}

	public function getCatalogs()
	{
		$catalogIds = (array) array_filter((array) json_decode($this->request['catalogs']));
		if (count($catalogIds) == 0) return Catalog::get();
		return Catalog::whereIn('id', $catalogIds)->get();
	}

	public function buildQuery()
	{
		$request = $this->request;
		$query = new CatalogProduct();
		if($request['feed_prices'] == 'withPrices')
		{
			$query = $query->join('catalog_price_product', function($join) use ($request){
											$join->on('catalog_products.id', '=', 'catalog_price_product.product_id')
												 ->where('catalog_price_product.price', '>=', $request['min_price'])
												 ->where('catalog_price_product.price', '<=', $request['max_price']);
										});
		}
		$query = $query->join('model_parts', function($join) use ($request){
									$join->on('catalog_products.id', '=', 'model_parts.part_id');
							     });
		$query = $query->where('catalog_products.active', '=', 'active');
		$catalogs = (array) array_filter((array) json_decode($request['catalogs']));
		$manufacturers = (array) array_filter((array) json_decode($request['manufacturer_id']));
		if (count($catalogs) > 0)
		{
			$query = $query->whereIn('catalog_id', $catalogs);
		} else $query = $query->where('catalog_id', '>', 0);
		if (count($manufacturers) > 0)
		{
			$query = $query->whereIn('manufacturer_id', $manufacturers);
		}	
		$query = $query->select('catalog_products.id', 'model_parts.id as part_id', 'catalog_products.active', 'catalog_products.title', 'catalog_products.meta_keywords', 'catalog_products.meta_title', 'catalog_products.meta_description', 'catalog_products.stock', 'catalog_products.slug', 'catalog_products.active', 'catalog_products.code', 'catalog_products.catalog_id', 'catalog_products.manufacturer_id', DB::raw("REPLACE(REPLACE(content, CHAR(13), ''), CHAR(10), '') as `content`"))->distinct();	
		//dd($query->toSql());
		return $query;		
	}
}