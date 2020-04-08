<?php

namespace App\Http\Libraries;
use App\Models\CatalogProduct;
use App\Models\Feed;
use Auth;

Class GetProductsFeed{

	protected $request;	

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function getProducts()
	{
		$feed = Feed::find($this->request['feed_id']);
		if($this->request['catalog'] == 'catalogs') {
			$products = $this->getCatalogProducts($this->request);
		} elseif($this->request['catalog'] == 'parts') {
			$products = $this->getParts($this->request);
		} else {
			$products = $this->getAllProducts($this->request);
		}

		$feed = app('App\Http\Libraries\Feeds\\'.$feed->class_name, [Auth::user()->email, $products, $this->request]);
        $feed->generateFeed();

	}

	public function getCatalogProducts($request)
	{
		if($request['feed_prices'] == 'all') {
			if(array_key_exists('manufacturer_id', $request)) {
				$products = CatalogProduct::Join('manufacturers', function($join) use($request){
									 $join->on('manufacturers.id', '=', 'catalog_products.manufacturer_id')
									 	  ->whereIn('manufacturers.id', $request['manufacturer_id']);
							})
							->Join('product_images', function($join){
								$join->on('product_images.product_id', '=', 'catalog_products.id');
							})
							->Join('catalog_category_product', function($join){
								$join->on('catalog_category_product.product_id', '=', 'catalog_products.id');
							})
							->select('catalog_products.*')
							->where('catalog_products.active', 'active')
							->where('catalog_products.catalog_id', '!=', 0)
							->groupBy('catalog_products.code')
							->get();
			} else {
				$products = CatalogProduct::Join('product_images', function($join){
								$join->on('product_images.product_id', '=', 'catalog_products.id');
							})
							->Join('catalog_category_product', function($join){
								$join->on('catalog_category_product.product_id', '=', 'catalog_products.id');
							})
							->select('catalog_products.*')
							->where('catalog_products.active', 'active')
							->where('catalog_products.catalog_id', '!=', 0)
							->groupBy('catalog_products.code')
							->get();
			}
		} else {
			$products = CatalogProduct::Join('catalog_price_product', function($join) use($request){
					                    $join->on('catalog_price_product.product_id', '=', 'catalog_products.id');
					                    if($request['min_price'] != '') {
					                    	$join->where('price', '>=', $request['min_price'])
					                    		 ->where('price', '<=', $request['max_price']);
					                    }
					 	})
						->Join('product_images', function($join){
							$join->on('product_images.product_id', '=', 'catalog_products.id');
						})
						->Join('catalog_category_product', function($join){
							$join->on('catalog_category_product.product_id', '=', 'catalog_products.id');
						});
			if(array_key_exists('manufacturer_id', $request)) {
				$products = $products->Join('manufacturers', function($join) use($request){
									 $join->on('manufacturers.id', '=', 'catalog_products.manufacturer_id')
									 	  ->whereIn('manufacturers.id', $request['manufacturer_id']);
							});
			}
			$products = $products->select('catalog_products.*')
								 ->where('catalog_products.active', 'active')
								 ->where('catalog_products.catalog_id', '!=', 0)
							     ->groupBy('catalog_products.code')
								 ->get();

		}

		return $products;
	}

	public function getParts($request)
	{
		//dd($request);
		if($request['feed_prices'] == 'all') {
			if(array_key_exists('manufacturer_id', $request)) {
				$products = CatalogProduct::Join('manufacturers', function($join) use($request){
									 		$join->on('manufacturers.id', '=', 'manufacturer_id')
									 	  		 ->whereIn('manufacturers.id', $request['manufacturer_id']);
							});
				
				$products = $products->Join('product_images', function($join){
											$join->on('product_images.product_id', '=', 'catalog_products.id');
										})
				                     ->Join('category_parts', function($join){
											$join->on('category_parts.part_id', '=', 'catalog_products.id');
										})
									 ->select('catalog_products.*')
									 ->where('catalog_products.catalog_id', 0);
			} else {
				$products = CatalogProduct::Join('product_images', function($join){
												$join->on('product_images.product_id', '=', 'catalog_products.id');
											})
											->Join('category_parts', function($join){
												$join->on('category_parts.part_id', '=', 'catalog_products.id');
											})
											->select('catalog_products.*')
											->where('catalog_products.catalog_id', 0);
			}

			if(array_key_exists('types', $request)) {
				$products = $products->Join('type_parts', function ($join) use($request){
									 $join->on('type_parts.part_id', '=', 'catalog_products.id')
									 	  ->whereIn('type_parts.type_id', $request['types']);
									});
			} elseif(array_key_exists('models', $request)) {
				$products = $products->Join('type_parts', function ($join){
									   $join->on('type_parts.part_id', '=', 'catalog_products.id');
									 })
									 ->Join('car_model_types', function ($join) use($request){
									   $join->on('car_model_types.id', '=', 'type_parts.type_id')
									        ->whereIn('car_model_types.model_id', $request['models']);
									 });			
			} elseif(array_key_exists('cars', $request)) {
				$products = $products->Join('type_parts', function ($join){
									   $join->on('type_parts.part_id', '=', 'catalog_products.id');
									 })
									 ->Join('car_model_types', function ($join){
									   $join->on('car_model_types.id', '=', 'type_parts.type_id');
									 })
									 ->Join('car_models', function ($join){
									   $join->on('car_models.id', '=', 'car_model_types.model_id');
									 })
									 ->Join('car_model_groups', function ($join){
									   $join->on('car_model_groups.id', '=', 'car_models.model_group_id');
									 })
									 ->Join('cars', function ($join) use($request){
									   $join->on('cars.id', '=', 'car_model_groups.car_id')
									        ->whereIn('cars.id', $request['cars']);
									 });

			} else { 
				$products = $products->Join('type_parts', function ($join){
									   $join->on('type_parts.part_id', '=', 'catalog_products.id');
									 })
									 ->Join('car_model_types', function ($join){
									   $join->on('car_model_types.id', '=', 'type_parts.type_id');
									 })
									 ->Join('car_models', function ($join){
									   $join->on('car_models.id', '=', 'car_model_types.model_id');
									 })
									 ->Join('car_model_groups', function ($join){
									   $join->on('car_model_groups.id', '=', 'car_models.model_group_id');
									 })
									 ->Join('cars', function ($join) use($request){
									   $join->on('cars.id', '=', 'car_model_groups.car_id');
									 });
			}

			$products = $products->distinct()->get();
		} else {
			$products = CatalogProduct::Join('catalog_price_product', function($join) use($request){
					                    $join->on('catalog_price_product.product_id', '=', 'catalog_products.id');
					                    if($request['min_price'] != '') {
					                    	$join->where('price', '>=', $request['min_price'])
					                    		 ->where('price', '<=', $request['max_price']);
					                    }
					 	});
			if(array_key_exists('manufacturer_id', $request)) {
				$products = $products->Join('manufacturers', function($join) use($request){
									 $join->on('manufacturers.id', '=', 'catalog_products.manufacturer_id')
									 	  ->whereIn('manufacturers.id', $request['manufacturer_id']);
							});
			}
			$products = $products->Join('product_images', function($join){
											$join->on('product_images.product_id', '=', 'catalog_products.id');
										})
								 ->Join('category_parts', function($join){
												$join->on('category_parts.part_id', '=', 'catalog_products.id');
										})
								 ->select('catalog_products.*')
								 ->where('catalog_products.catalog_id', 0);
							 
			if(array_key_exists('types', $request)) {
				$products = $products->Join('type_parts', function ($join) use($request){
									 $join->on('type_parts.part_id', '=', 'catalog_products.id')
									 	  ->whereIn('type_parts.type_id', $request['types']);
									});
			} elseif(array_key_exists('models', $request)) {
				$products = $products->Join('type_parts', function ($join){
									   $join->on('type_parts.part_id', '=', 'catalog_products.id');
									 })
									 ->Join('car_model_types', function ($join) use($request){
									   $join->on('car_model_types.id', '=', 'type_parts.type_id')
									        ->whereIn('car_model_types.model_id', $request['models']);
									 });			
			} elseif(array_key_exists('cars', $request)) {
				$products = $products->Join('type_parts', function ($join){
									   $join->on('type_parts.part_id', '=', 'catalog_products.id');
									 })
									 ->Join('car_model_types', function ($join){
									   $join->on('car_model_types.id', '=', 'type_parts.type_id');
									 })
									 ->Join('car_models', function ($join){
									   $join->on('car_models.id', '=', 'car_model_types.model_id');
									 })
									 ->Join('car_model_groups', function ($join){
									   $join->on('car_model_groups.id', '=', 'car_models.model_group_id');
									 })
									 ->Join('cars', function ($join) use($request){
									   $join->on('cars.id', '=', 'car_model_groups.car_id')
									        ->whereIn('cars.id', $request['cars']);
									 });

			} else {
				$products = $products->Join('type_parts', function ($join){
									   $join->on('type_parts.part_id', '=', 'catalog_products.id');
									 })
									 ->Join('car_model_types', function ($join){
									   $join->on('car_model_types.id', '=', 'type_parts.type_id');
									 })
									 ->Join('car_models', function ($join){
									   $join->on('car_models.id', '=', 'car_model_types.model_id');
									 })
									 ->Join('car_model_groups', function ($join){
									   $join->on('car_model_groups.id', '=', 'car_models.model_group_id');
									 })
									 ->Join('cars', function ($join){
									   $join->on('cars.id', '=', 'car_model_groups.car_id');
									 });
			}

			$products = $products->distinct()->get();				 
		}
		return $products;
	}

	public function getKits($request) 
	{
		dd($request);
	}

	public function getAllProducts($request)
	{
		//dd($request);
		if($request['feed_prices'] == 'all') {
			if(array_key_exists('manufacturer_id', $request)) {
				$products = CatalogProduct::Join('manufacturers', function($join) use($request){
									 $join->on('manufacturers.id', '=', 'catalog_products.manufacturer_id')
									 	  ->whereIn('manufacturers.id', $request['manufacturer_id']);
							})
							->Join('product_images', function($join){
								$join->on('product_images.product_id', '=', 'catalog_products.id');
							})
							->select('catalog_products.*')
							->where('catalog_products.active', 'active')
							->groupBy('catalog_products.code')
							->get();
			} else {
				$products = CatalogProduct::Join('product_images', function($join){
								$join->on('product_images.product_id', '=', 'catalog_products.id');
							})
							->select('catalog_products.*')
							->where('catalog_products.active', 'active')
							->groupBy('catalog_products.code')
							->get();
			}
		} else {
			$products = CatalogProduct::Join('catalog_price_product', function($join) use($request){
					                    $join->on('catalog_price_product.product_id', '=', 'catalog_products.id');
					                    if($request['min_price'] != '') {
					                    	$join->where('price', '>=', $request['min_price'])
					                    		 ->where('price', '<=', $request['max_price']);
					                    }
					 	})
						->Join('product_images', function($join){
							$join->on('product_images.product_id', '=', 'catalog_products.id');
						});
			if(array_key_exists('manufacturer_id', $request)) {
				$products = $products->Join('manufacturers', function($join) use($request){
									 $join->on('manufacturers.id', '=', 'catalog_products.manufacturer_id')
									 	  ->whereIn('manufacturers.id', $request['manufacturer_id']);
							});
			}
			$products = $products->select('catalog_products.*')
								 ->where('catalog_products.active', 'active')
							     ->groupBy('catalog_products.code')
								 ->get();

		}
	return $products;	
	}

}