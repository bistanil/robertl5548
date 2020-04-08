<?php

namespace App\Http\Libraries;
use App;
use App\Models\Webservice;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\Manufacturer;
use App\Models\Currency;
use App\Models\Company;
use App\Models\AutonetSearchedCode;
use Carbon\Carbon;
use GuzzleHttp\Client;

Class Autonetws{

	protected $product;	
	protected $ws;
	protected $currency;
	protected $checkInterval;

	public function __construct()
	{
		$this->setCurrency();
		$webservices = Webservice::whereTitle('Autonet')->get();
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
				$url = 'https://wes.autonet.ro/ArticleOffer/GetArticleOffers';
			  	$ch = curl_init();
			  	curl_setopt( $ch, CURLOPT_URL, $url );
			  	curl_setopt( $ch, CURLOPT_POST, true );
			  	curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml; charset=utf-8','Accept: Application/xml', 'TAX-CODE: '.$this->ws->username, 'SECURITY-TOKEN: '.$this->ws->key));
			  	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			  	if ($this->product->manufacturer->manufacturer_nr > 0)
			  	{
				  	curl_setopt( $ch, CURLOPT_POSTFIELDS, "<Articles>  
															  <Article>
															    <TDArticleNo>".$this->product->code."</TDArticleNo>
															    <TDBrandId>".$this->product->manufacturer->manufacturer_nr."</TDBrandId>
															  </Article>
															</Articles>" );
				} else {
					curl_setopt( $ch, CURLOPT_POSTFIELDS, "<Articles>  
															  <Article>
															  	<PartNo>".$this->product->code."</PartNo>															    
															  </Article>
															</Articles>" );
				}
			  	$result = simplexml_load_string(curl_exec($ch));
			  	curl_close($ch);
			  	if ($result != false)
				  	if($result->ArticleOffers->ArticleOffer->PriceWoVat > 0)
				  	{
					  	$company = Company::whereDefault('yes')->get()->first();
                    	$tva = intval($company->vat_percentage)/100+1;
					  	$price = new ProductPrice();
					  	$price->source = 'autonet';
					  	$price->supplier_id = 1;
					  	$price->product_id = $this->product->id;
					  	$price->currency_id = $this->currency->id;
					  	$price->price = $result->ArticleOffers->ArticleOffer->PriceWoVat*$tva;
					  	$price->acquisition_price = $ArticleOffer->PriceWoVat*$tva;
					  	$price->save();
					}
				$this->saveCheck();
			}
		}		
	}

	public function checkProduct()
	{
		$searched = AutonetSearchedCode::wherePart_id($this->product->id)->get();
		if ($searched->count() > 0)
		{
			$searched = $searched->first();
			$searchedDate = Carbon::parse($searched->searched_at);
			if ($searchedDate->diffInDays() <= $this->checkInterval) return FALSE;
			return TRUE;
		}
		return TRUE;
	}

	public function saveCheck()
	{
		$checks = AutonetSearchedCode::wherePart_id($this->product->id)->get();
		if ($checks->count() > 0) $check = $checks->first();
		else $check = new AutonetSearchedCode();
		$check->part_id = $this->product->id;
		$check->searched_at = Carbon::now();
		$check->save();
	}

	public function processList($parts)
	{
		if ($this->ws != null)
		{
			$url = 'https://wes.autonet.ro/ArticleOffer/GetArticleOffers/';
			$client = new Client();
			$response = $client->request('POST', $url, [
							'curl' => [
								CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
								CURLOPT_INTERFACE => '213.136.74.59',
							],
							'headers'  => [
				                'Accept' => 'application/json',
				                'Content-Type' => 'application/json',
				                'TAX-CODE' => $this->ws->username,
				                'SECURITY-TOKEN' => $this->ws->key,
				            ],
							//'body' => json_encode([['TDBrandId' => 161, 'TDArticleNo' => 'BCH720' ]] )
							'body' => $this->buildRequestString($parts)
						])->getBody()->getContents();
			//dd($response);
			foreach (json_decode($response)->ArticleOffers as $key => $ArticleOffer) {			  		
		  		if(floatval($ArticleOffer->PriceWoVat) > 0)
			  	{
			  		//dd(json_decode($response));
			  		//dd($ArticleOffer);
			  		$part = $this->getPart($ArticleOffer);
			  		if ($part != null)
			  		{
			  			$company = Company::whereDefault('yes')->get()->first();
                    	$tva = intval($company->vat_percentage)/100+1;
			  			$price = new ProductPrice();
					  	$price->source = 'autonet';
					  	$price->supplier_id = 1;
					  	$price->product_id = $part->id;
					  	$price->currency_id = $this->currency->id;
					  	$price->acquisition_price = $ArticleOffer->PriceWoVat*$tva;
					  	$price->price = $ArticleOffer->PriceWoVat*$tva;
					  	echo $part->id.' ';
					  	$price->save();
					  	$this->setProduct($part);
					  	$this->saveCheck();
					}
				}
				
			}							
		}		
	}

	public function buildRequestString($parts)
	{
		$partsArr = [];
		foreach ($parts as $key => $part) {
			if ($part->product != null)
				if ($part->product->code != '' && $part->product->manufacturer->td_id != '')
					$partsArr[$key] = [
									'TDBrandId' => $part->product->manufacturer->td_id,
									'TDArticleNo' => $part->product->code,
								];
		}
		//print_r($partsArr);		
		return json_encode($partsArr);
	}

	public function getPartOld($ArticleOffer)
	{
		$part = CatalogProduct::whereSearch_code(preg_replace("/[^a-zA-Z0-9]+/","", $ArticleOffer->PartNo))->get();
		if ($part->count() > 0) return $part->first();
		return null;
	}

	public function getPart($ArticleOffer)
	{
		$manufacturer = Manufacturer::whereTd_id($ArticleOffer->TDBrandId)->get();
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