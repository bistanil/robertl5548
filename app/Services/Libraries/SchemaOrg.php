<?php namespace App\Services\Libraries;

use App;
use Spatie\SchemaOrg\Schema;

class SchemaOrg{

	public function product($product)
	{		
		$schema = Schema::product()
						->brand('AutoSam.ro')
						->manufacturer($product->manufacturer->title)
						->name(productTitle($product))
						->mpn($product->code)
						->description(preg_replace('/(\v|\s)+/', ' ', strip_tags($product->content)))
						->url(frontProductPath($product, $product->catalog_id))
						->category(getCategory($product));
		if($product->reviews->where('status','approved')->count() > 0) $schema->aggregateRating([
											'ratingValue' => $product->reviews->where('status','approved')->avg('rating'), 
											'ratingCount' => $product->reviews->where('status','approved')->count(), 
											'reviewCount' => $product->reviews->where('status','approved')->count()
										  ]);
		else $schema->aggregateRating([
											'ratingValue' => 5, 
											'ratingCount' => 2, 
											'reviewCount' => 2
										  ]);
		foreach($product->reviews->where('status','approved') as $key => $review) {
			$schema->review([
									'author' => $review->name, 
									'datePublished' => $review->created_at, 
									'description' => $review->content,
									'name' => $review->title
								  ]);
		}
		if (count($product->images) > 0) $schema->image(asset(imageExists(config('hwimages.product.destination').$product->images->sortBy('position')->first()->image, 'product')));		
		if (finalPrice($product) > 0) $schema->offers(['price' => finalPrice($product), 'priceCurrency' => 'RON', 'mpn' => $product->code, 'availability' => $this->availability($product), 'url' => frontProductPath($product, $product->catalog_id)]);		
		return $schema->toScript();
	}

	private function availability($product)
	{
		if ($product->stock == 'in_stock') return 'http://schema.org/InStock';
		if ($product->stock == 'in_supplier_stock') return 'http://schema.org/PreOrder';
		if ($product->stock == 'not_in_stock') return 'http://schema.org/OutOfStock';
	}

}