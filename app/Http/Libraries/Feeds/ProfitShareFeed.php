<?php

namespace App\Http\Libraries\Feeds;

use App\Models\CatalogProduct;
use App\Models\CatalogCategory;
use App\Models\Company;
use App\Models\Feed;
use Storage;
use Mail;
use Config;
use URL;
use App;
use Excel;

Class ProfitShareFeed{

	protected $feed;
	protected $products;

	public function __construct($request)
	{
		$this->request = $request;
	}	

	public function generateFeed()
	{
		$provider = new CatalogProductsFeed($this->request);
		$total = $provider->getTotal();  
		echo $total.' ';
	    $offset = 0;
	    $limit = 10000;
		$products = $provider->getProducts($limit, $offset);
		$company = Company::whereDefault('yes')->get()->first();
		$fileName = str_slug($this->request['file_name'], '-');
		if ($products != null)
		{
			
			$excel = App::make('excel');
	        //return view('admin.partials.catalogs.products.excel', compact('catalog'));
	        Config::set('excel.csv.delimiter', ',');
	        Excel::create($fileName, function($excel) use ($products, $company, $fileName) {
	            $excel->sheet($fileName, function($sheet) use ($products, $company) {
	                $sheet->loadView('admin.partials.catalogs.products.csv.csvProfitshare')
	                      ->with('products', $products)
	                      ->with('company', $company);
	            })->store('csv', public_path('files/feeds'));
	        });      

	        $url = Config::get('app.url').'public/files/feeds/'.$fileName.'.csv';
        }      
	}	

	public function generateFeedOld()
	{
		$products = $this->getProducts();
		$company = Company::whereDefault('yes')->get()->first();
		if ($products != null)
		{
			$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>'.'<products/>');
		    foreach($products as $key=>$product)
		    {
		        if($product->catalog_id == 0) {
		            $category = implode(' > ',$product->partsCategories->pluck('title')->toArray());
		        } else {
		            $category = CatalogCategory::find($product->categories()->get()->first()->category_id)->whereActive('active')->whereParent(0)->get()->first();
		        }
		        
		        if($product->catalog_id == 0) {
		            $url = Config::get('app.url').'piese-auto/'.$product->slug;
		        } else {
		            $url = Config::get('app.url').'produs/'.$product->slug;
		        } 
		        $item = $xml->addChild('product');
		        $adv_name = $item->addChild('adv_name', URL::to('/'));
		        if($product->catalog_id == 0) {
		            $category = $item->addChild('category', $category);
		        } else {
		            $category = $item->addChild('category', $category->title);  
		        }
		        $manufacturer = $item->addChild('manufacturer', $product->manufacturer->title);
		        $productCode = $item->addChild('product_code', $product->code);
		        $title = $item->addChild('product_name', $product->title);
		        $description = $item->addChild('product_desc', $product->short_description);
		        if ($product->images->count() > 0) { 
		            $image = $product->images->sortBy('position')->first()->image;
		            $imageUrl = Config::get('app.url').config('hwimages.product.destination').$image;
		            $image_url = $item->addChild('product_pic', $imageUrl);
		        }
		        $price = (int)$product->prices->min('price');
		        $valTva = $company->vat/(1+$company->vat)*$price;
		        $priceNoVat = $item->addChild('price_no_vat', $price-$valTva);
		        $priceVat = $item->addChild('price_vat', $valTva);
		        $currency = $item->addChild('currency', defaultCurrency());	           
		    }
			
		    if(Storage::disk('xml')->put('profitshare.xml', $xml->asXML())) {
		        $url = Config::get('app.url').'public/files/feeds/profitshare.xml';
		        Mail::send('admin.emails.feedFinished', ['url' => $url], function ($message) use ($url) {
		            $message->from(config('mail.defaultEmail'), 'Admin');
		            $message->subject(trans('admin/emails.feedFinished'));
		            $message->to($this->email);
		        });
			}	
			//dd($products);		
        }        
	}

	private function setFeed()
	{
		$this->feed = Feed::where('title','profitshare')->get()->first();
	}

	private function getProducts()
	{
		if ($this->feed != null)
		{
			$feed = $this->feed;
			return CatalogProduct::join('products_feed', function ($join) use ($feed) { 
                                            $join->on('catalog_products.id', '=', 'products_feed.product_id')
                                                 ->where('products_feed.feed_id', '=', $feed->id);
                                    })
                                    ->select('catalog_products.*')
                                    ->get();
		}
		return null;
	}

}