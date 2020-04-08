<?php

namespace App\Http\Libraries;
use App;
use App\Models\Webservice;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\Manufacturer;
use App\Models\Currency;
use App\Models\Supplier;
use App\Models\Elit\ElitSearchedCode;
use App\Models\Elit\ElitCode;
use Carbon\Carbon;
use nusoap_client;

Class Elitws{

	protected $product;	
	protected $ws;
	protected $currency;
	protected $checkInterval;

	public function __construct()
	{
		$this->setCurrency();
		$webservices = Webservice::whereTitle('Elit')->get();
		if ($webservices->count() > 0) $this->ws = $webservices->first();
		else $this->ws = null;		
	}

	public function setProduct($product)
	{
		$this->product = $product;
		$this->setCheckInterval();
	}

	public function setCurrency()
	{
		$this->currency = Currency::whereDefault('yes')->get()->first();
	}

	public function setCheckInterval()
	{
		if ($this->product->price != null) $this->checkInterval = 2;
		else $this->checkInterval = 7;
	}

	public function process()
	{
		if ($this->ws != null)
		{
			if ($this->checkProduct())
			{
				$wshost = "http://90.183.212.240:7606/InterCompany-1.34.0/BuyerService?wsdl";
				$namespace = "http://buyer.elit.cz/";
				$company = "ELIT_RO"; // ELIT_CZ
				$username = $this->ws->username;
				$password = $this->ws->key;
				$part_no = $this->getPartNo();
				if ($part_no != null)
				{
				  	//$paramsInfo = array('company' => $company, 'login' => $username, 'password' => $password, 'itemNo' => 'A6111800009');
				  	$paramsInfo = array('company' => $company, 'login' => $username, 'password' => $password, 'itemNo' => $part_no);
				  	$client = new nusoap_client($wshost);
				  	//$result = $client->call('getItem', $params, $namespace, $namespace, false, true);
				  	$result = $client->call('getItemInfo', $paramsInfo, $namespace, $namespace, false, true);
				  	//dd($result);				  	
				  	if(isset($result['retailPrice']))
				  	{
				  		$price = $this->checkPrice();
				  		$supplier = Supplier::whereTitle('Elit')->get()->first();
					  	if ($price == FALSE) $price = new ProductPrice();
					  	$price->source = 'elit';
					  	$price->supplier_id = $supplier->id;
					  	$price->product_id = $this->product->id;
					  	$price->currency_id = $this->currency->id;
					  	$price->acquisition_price_no_vat = round($result['retailPrice']*(1-$result['discountPct']/100), 2);
					  	$price->price_no_vat = round($result['retailPrice']*(1-$result['discountPct']/100), 2);
					  	$price->acquisition_price = round($result['retailPrice']*(1-$result['discountPct']/100)*(1+$result['vatPct']/100), 2);
					  	$price->price = round($result['retailPrice']*(1-$result['discountPct']/100)*(1+$result['vatPct']/100), 2);
					  	if ($price->price > 0) $price->save();
					}
				}
				$this->saveCheck();
			}
		}		
	}

	private function getPartNo()
	{
		if (isset($this->product->manufacturer))
		{
			$partNo = ElitCode::whereManufacturer_id($this->product->manufacturer->id)->wherePart_id($this->product->id)->get();
			if ($partNo->count() > 0) return $partNo->first()->elit_code;
			return null;
		}
	}

	public function checkProduct()
	{
		$searched = ElitSearchedCode::wherePart_id($this->product->id)->get();
		if ($searched->count() > 0)
		{
			$searched = $searched->first();
			$searchedDate = Carbon::parse($searched->searched_at);
			if ($searchedDate->diffInDays() <= $this->checkInterval) return FALSE;
			return TRUE;
		}
		return TRUE;
	}

	public function checkPrice()
	{
		$supplier = Supplier::whereTitle('Elit')->get()->first();
		$prices = ProductPrice::whereProduct_id($this->product->id)->whereSupplier_id($supplier->id)->get();
		if ($prices->count() > 0) return $prices->first();
		else return FALSE;
	}

	public function saveCheck()
	{
		$checks = ElitSearchedCode::wherePart_id($this->product->id)->get();
		if ($checks->count() > 0) $check = $checks->first();
		else $check = new ElitSearchedCode();
		$check->part_id = $this->product->id;
		$check->searched_at = Carbon::now();
		$check->save();
	}

	public function getPart($ArticleOffer)
	{
		$manufacturer = Manufacturer::whereManufacturer_nr($ArticleOffer->TDBrandId)->get();
		if ($manufacturer->count() > 0)
		{
			$manufacturer = $manufacturer->first();
			$part = CatalogProduct::whereManufacturer_id($manufacturer->id)->whereSearch_code(preg_replace("/[^a-zA-Z0-9]+/","", $ArticleOffer->TDArticleNo))->get();
			if ($part->count() > 0) return $part->first();
			return null;
		}
		return null;
	}

}