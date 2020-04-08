<?php

namespace App\Http\Libraries;
use App\Http\Libraries\Price;
use App\Models\Currency;
use App\Models\Client;
use App\Models\Discount;
use App\Models\Company;
use App\Models\TransportType;
use App\Models\PartsCategory;
use App\Models\Feed;
use App\Models\CatalogProduct;
use App\Models\Manufacturer;
use App\Models\Catalog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\AwbWeight;
use App\Models\AwbContactPerson;
use App\Models\UserProfile;
use Cart;
use URL;

Class DropdownOptions {

	public function active()
	{
		return [
			'active' => trans('admin/common.active'),
			'inactive' => trans('admin/common.inactive')
		];
	}

	public function aclGroups($groups)
	{
		$options = ['first_level' => 'Sectiune noua'];
		foreach ($groups as $key => $group) {			
			$options[$group->group] = $group->group;
		}
		return $options;
	}

	public function language()
	{
		return [
			'ro' => trans('admin/common.romanian'),		
		];	
	}

	public function menu()
	{
		return [
			'top' => trans('admin/pages.top'),
			'footer' => trans('admin/pages.footer'),
			'terms' => trans('admin/pages.terms'),
			'policy' => trans('admin/pages.policy'),
			'cookies' => trans('admin/pages.cookies'),
			'withdrawal' => trans('admin/pages.withdrawal'),
			//'return' => trans('admin/pages.return'),
			//'warranty' => trans('admin/pages.warranty'),
		];	
	}

	public function accountAction()
	{
		return [
			'' => trans('front/common.selectItem'),
			'delete' => trans('front/forms.delete'),
			'suspend' => trans('front/forms.suspend'),
			'nothing' => trans('front/forms.nothing'),
		];
	}

	public function logoType()
	{
		return [
			'front' => trans('admin/common.front'),
			'proforma' => trans('admin/common.proforma'),
			//'footer' => trans('admin/common.footer'),
			'favicon' => trans('admin/common.favicon'),
			'inEmail' => trans('admin/common.inEmail'),
		];
	}

	public function priceOrder()
	{
		return [
			'crescator' => trans('front/common.asc'),
			'descrescator' => trans('front/common.desc'),
		];	
	}

	public function carTypes()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'car' => trans('admin/cars.cars'),
			'motorcycle' => trans('admin/cars.motorcycles'),
			'truck' => trans('admin/cars.trucks'),
			'other' => trans('admin/cars.other')
		];
	}	

	public function idTitle2($items)
	{
		$options=['' => trans('front/common.selectItem')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->title;
		}
		return $options;
	}

	public function idTitleSelect($items)
	{
		$options=['' => trans('admin/common.selectItem')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->title;
		}
		return $options;
	}

	public function categories($items)
	{
		$options=['' => trans('admin/common.selectItem')];		
		foreach ($items as $item) {
			$optionTitle = '';
			$ancestors = ancestors($item);
			foreach ($ancestors as $key => $ancestor) {
				if ($ancestor->parent != 0) $optionTitle .= $ancestor->title.' '.$ancestor->td_id.' - ';				
			}
			if ($optionTitle != '') $options[$item->id] = $optionTitle;
		}
		return $options;
	}

	public function idTitleNull($items)
	{
		$options=['0' => trans('admin/common.selectItem')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->title;
		}
		return $options;
	}
	
	public function transportTypes()
	{
		$options = ['' => trans('admin/common.selectItem')];
		$transportTypes = TransportType::whereActive('active')->get();
		foreach($transportTypes as $transportType) {
			$options[$transportType->id] = $transportType->type;
		}
		return $options;
	}

	public function watermark()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'catalogCategoryWatermark' => trans('admin/settings.catalogCategoryWatermark'),
			'productWatermark' => trans('admin/settings.productWatermark')
		];	
	}

	public function socialmedia()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'Facebook' => 'Facebook',
			'Twitter' => 'Twitter',
			'Skype' => 'Skype',
			'Instagram' => 'Instagram',
			'LinkedIn' => 'LinkedIn',
			'Google Plus' => 'Google Plus',
			'Youtube' => 'Youtube'
		];	
	}

	public function script()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'header' =>  trans('admin/settings.header'),
			'body' =>  trans('admin/settings.body'),
			'footer' =>  trans('admin/settings.footer'),
			'socialShare' => trans('admin/settings.socialShare'),
		];	
	}

	public function staticmeta()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'Home' => trans('admin/settings.staticmetaHome'),
			'Contact' => trans('admin/settings.staticmetaContact'),
			'RequestOffer' => trans('admin/settings.staticmetaRequestOffer'),
			'Register' => trans('admin/settings.staticmetaRegister'),
			'Login' => trans('admin/settings.staticmetaLogin'),
			'Search' => trans('admin/settings.staticmetaSearch'),
			'Cart' => trans('admin/settings.staticmetaCart'),
			'ClientAccount' => trans('admin/settings.staticmetaClientAccount'),
			'ThankYouPage' => trans('admin/settings.staticmetaThankYouPage'),
			'Blog' => trans('admin/settings.staticmetaBlog'),
			'Catalogs' => trans('admin/settings.staticmetaCatalogs'),
			'SpecialOffers' => trans('admin/settings.staticmetaSpecialOffers'),
			//'Careers'=>trans('admin/settings.staticmetaCareers'),
			//'Suggestions'=>trans('admin/settings.staticmetaSuggestions'),
			'ReturnProduct' => trans('admin/settings.staticmetaReturnProduct'),
		];	
	}

	public function yesno()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'no' => trans('admin/common.no'),
			'yes' => trans('admin/common.yes'),
		];	
	}

	public function defaultYes()
	{
		return [
			'yes' => trans('admin/common.yes'),
			'no' => trans('admin/common.no'),
		];	
	}

	public function perPage()
	{
		return [
			15 => 15,
			30 => 30,
			60 => 60,
			90 => 90
		];	
	}

	public function idTitle($items)
	{
		$options=['' => trans('admin/common.selectItem')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->title;
		}
		return $options;
	}

	public function marginType($items)
	{
		$options=['' => trans('admin/common.selectItem')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->type;
		}
		return $options;
	}

	public function idTitleDefault($items)
	{
		$options=[];		
		foreach ($items as $item) {
			$options[$item->id]=$item->title;
		}
		return $options;
	}

	public function idValue($items)
	{
		$options=['' => trans('admin/common.selectItem')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->value;
		}
		return $options;
	}

	public function ValueValue($items)
	{
		$options=['' => trans('admin/common.selectItem')];		
		foreach ($items as $item) {
			$options[$item->value]=$item->value;
		}
		return $options;
	}

	public function stock()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'in_stock' => trans('admin/catalogs.in_stock'),
			'in_supplier_stock' => trans('admin/catalogs.in_supplier_stock'),
			'not_in_stock' => trans('admin/catalogs.not_in_stock')
		];	
	}

	public function gender()
	{
		return [
			'male' => trans('admin/clients.male'),
			'female' => trans('admin/clients.female')
		];	
	}

	public function frontGender()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'female' => trans('admin/clients.female'),
			'male' => trans('admin/clients.male')
		];	
	}

	public function productPricesOld($product)
	{
		$options = [];
		foreach ($product->prices as $price) {
			if ($price->source == 'admin') $options[$price->id]=$price->source.' '.$price->price.' '.$price->currency->code;
			else {
				$finalPrice = new Price();
				$finalPrice = $finalPrice->finalPrice($product, $price);
				$options[$price->id]=$price->source.' '.$finalPrice.' '.$price->currency->code;
			}
		}
		return $options;
	}

	public function productPrices($product)
	{
		$options = [];
		foreach ($product->prices->sortBy('price') as $price) {
			if ($price->source == 'admin') $options[$price->id] = trans('admin/catalogs.supplier').': '.$price->supplier->title.' - '.trans('admin/catalogs.acquisitionPrice').': '.$price->acquisition_price.' - '.trans('admin/catalogs.finalPrice').': '.$price->price.' '.$price->currency->code;
			else {
				$finalPrice = new Price();
				$finalPrice = $finalPrice->salePrice($product, $price);
				$options[$price->id] = trans('admin/catalogs.supplier').': '.$price->supplier->title.' - '.trans('admin/catalogs.acquisitionPrice').': '.$price->acquisition_price.' - '.trans('admin/catalogs.finalPrice').': '.$finalPrice.' '.$price->currency->code;
			}
		}
		return $options;
	}

	public function currencies()
	{
		$currencies = Currency::all();
		$options = [];
		foreach ($currencies as $currency) {
			$options[$currency->id]=$currency->title.' '.$currency->code;			
		}
		return $options;
	}

	public function clients()
	{
		$clients = Client::all();		
		$options = ['' => trans('admin/common.selectItem')];
		foreach ($clients as $client) {
			$options[$client->id]=$client->name.' '.$client->email.' '.$client->phone;						
		}		
		return $options;
	}

	public function discounts()
	{
		$discounts = Discount::where('client_id', '=', '')->get();
		$options = ['' => trans('admin/common.selectItem')];
		foreach ($discounts as $discount) {
			$discountValue=(1-$discount->discount)*100;
			$options[$discount->id]=$discountValue.'%';			
		}
		return $options;
	}

	public function companies()
	{
		$companies = Company::all();
		$options = [];
		foreach ($companies as $company) {
			$options[$company->id]=$company->title.' '.$company->vat_code;			
		}
		return $options;
	}

	public function clientCompanies($client)
	{
		$options = ['' => trans('admin/common.selectItem')];
		foreach ($client->companies as $company) {
			$options[$company->id]=$company->title.' '.$company->fiscal_code;			
		}
		$options['newCompany'] = trans('front/clients.addCompany');
		return $options;
	}

	public function clientAddresses($client)
	{
		$options = ['' => trans('admin/common.selectItem')];		
		foreach ($client->deliveryAddresses as $address) {
			$options[$address->id]=$address->county.' - '.$address->city.' - '.$address->address;			
		}

		$options['newAddress'] = trans('front/clients.addAddress');
		return $options;
	}

	public function cars($cars)
	{
		$options = ['' => trans('admin/cars.selectCar')];
		foreach ($cars as $car) {
			$options[$car->id] = $car->title;			
		}
		return $options;
	}

	public function models($models)
	{
		$options = ['' => trans('admin/cars.selectModel')];
		foreach ($models as $model) {
			$options[$model->id] = $model->title.' - '.$model->construction_start_year.' - '.$model->construction_end_year;			
		}
		return $options;
	}

	public function types($types)
	{
		$options = ['' => trans('admin/cars.selectType')];
		foreach ($types as $type) {

				foreach($type->engines as $engine) {
					if(!empty($engine)) {
						$engine = $engine->code;
					} else {
						$engine = ' ';
					}	 
				}
			if(isset($engine)) {
				$options[$type->id]=$type->title.' - '.$type->construction_start_year.' - '.$type->construction_end_year.' - '.$type->kw.'KW - '.$type->hp.' CP'.' - '.$engine;
			} else {
				$options[$type->id]=$type->title.' - '.$type->construction_start_year.' - '.$type->construction_end_year.' - '.$type->hp.' CP';
			}			
		}
		return $options;
	}
	
	public function newsStatus()
	{
		return [
			'new' => trans('admin/common.new'),
			'approved' => trans('admin/common.approved'),
			'rejected' => trans('admin/common.rejected'),
		];
	}

	public function orderStatus()
	{
		return [
			'new' => trans('admin/orders.new'),
			'validated' => trans('admin/orders.validated'),
			'cancelled' => trans('admin/orders.cancelled'),
			'rejected' => trans('admin/orders.rejected'),
			'in_progress' => trans('admin/orders.in_progress'),
			'delivered' => trans('admin/orders.delivered'),
			'received' => trans('admin/orders.received'),
		];
	}

	public function webservices()
	{
		return [
			'Autonet' => 'Autonet',
			//'Bennett' => 'Bennett',	
			'Elit' => 'Elit',		
		];
	}

	public function returnBack() {
		return [
			'' => trans('admin/common.selectItem'),
			'cash' => trans('front/returnedProducts.cash'),
			'anotherProduct' => trans('front/returnedProducts.anotherProduct')
		];	
	}

	public function orderNumbers($orders)
	{
		$options = ['' => trans('admin/common.selectItem')];
		foreach($orders as $order) {
			$options[$order->id] = $order->id;
		}

		return $options;
	}

	public function orderItems($items)
	{
		$options=[];
		foreach ($items as $item) {
			$options[$item->product_id]=$item->title;
		}
		return $options;
	}

	public function orderItemsQty($items)
	{
		$options=[];		
		foreach ($items as $item) {
			$options[$item->qty] = $item->qty;
		}
		return $options;
	}

	public function bannerType() {
		return [
			'general' => trans('admin/banners.general'),
			//'home' => trans('admin/banners.home'),
			//'sidebar' => trans('admin/banners.sidebar')
		];	
	}

	//Feeds
	public function feeds()
	{
		$feeds = Feed::whereActive('active')->get();
		$options = ['' => trans('admin/common.selectItem')];
		foreach($feeds as $feed) {
			$options[$feed->id] = $feed->title;
		}

		return $options;
	}

	public function feedTypes()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'catalogs' => trans('admin/feeds.catalogProducts'),
			'parts' => trans('admin/feeds.catalogParts'),
		];
	}

	public function feedPrices()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'all' => trans('admin/common.allProducts'),
			'withPrices' => trans('admin/common.withPrices'),
		];
	}

	public function catalogsFeed()
	{
		$catalogs = Catalog::orderBy('title')->get();
		$options = ['' => trans('admin/common.selectItem')];
		foreach ($catalogs as $key => $catalog) {
			$options[$catalog->id] = $catalog->title;
		}
		return $options;
	}

	//End feeds

	//AWBS

	public function serviceType()
	{
		return [
			'collectorAccount' => trans('admin/awbs.collectorAccountType'),
			'standard' => trans('admin/awbs.standardType'),
		];
	}

	public function dimensions()
	{
		$options = ['' => trans('admin/common.selectItem')];
		$dimensions = AwbWeight::get();
		foreach($dimensions as $dimension) {
			$options[$dimension->id] = 'G: '.$dimension->weight.'Kg - L: '.$dimension->lenght.'cm - l: '.$dimension->width.'cm - H: '.$dimension->height.'cm';
		}
		return $options;
	}

	public function comments()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'urgentDelivery' => trans('admin/awbs.urgentDelivery'),
			'saturdayDelivery' => trans('admin/awbs.saturdayDelivery'),
			'mondayDelivery' => trans('admin/awbs.mondayDelivery'),
			'phoneContact' => trans('admin/awbs.phoneContact'),
			'fragile' => trans('admin/awbs.fragile'),
			'after16Delivery' => trans('admin/awbs.saturdayDelivery'),
			'between9And17delivery' => trans('admin/awbs.saturdayDelivery'),
		];
	}

		
	public function expeditionPayment()
	{
		return [
			'recipient' => trans('admin/awbs.recipient'),
			'sender' => trans('admin/awbs.sender'),
		];
	}

	public function deliveryOptions()
	{
		return [
			'' => trans('admin/common.selectItem'),
			'deliveryOnFanCourier' => trans('admin/awbs.deliveryOnFanCourier'),	
		];	
	}

	public function contactPeople() {
		$people = AwbContactPerson::whereActive('active')->get();
		foreach($people as $key=>$person)
		{
			$options[$person->id] = $person->name.' - '.$person->phone;	
		}
		return $options;
	}

	public function typeCredentials()
	{
		return [
			'fanCourier' => trans('admin/awbs.fanCourier'),	
		];	
	}

	//END AWBS

	public function counties($items)
	{
		$options=['' => trans('front/orders.selectCounty')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->title;
		}
		return $options;
	}

	public function cities($items)
	{
		$options=['' => trans('front/orders.selectCity')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->title;
		}
		return $options;
	}

	public function paymentMethod() 
	{
		return [
			'' => trans('front/orders.paymentMethodSelect'),
			'onDelivery' => trans('front/orders.onDelivery'),
			'paymentOrder' => trans('front/orders.paymentOrder'),

		];
	}

	public function gatewayTypes()
	{
		return [
			'' => trans('front/orders.paymentMethodSelect'),
			'onDelivery' => trans('front/orders.onDelivery'),
			'mobilpay' => trans('front/orders.mobilpay'),
			'paymentOrder' => trans('front/orders.paymentOrder'),
			
		];
	}

	public function codesList($codes)
	{
		foreach($codes as $code) {
			$product = CatalogProduct::whereId($code->product_id)->get()->first();
			$title = $product->title;
			$productCode = $product->code;
			$manufacturer = Manufacturer::whereId($product->manufacturer_id)->get()->first()->title;
			$options[$manufacturer.' - '.$productCode.' - '.$title] = $manufacturer.' - '.$productCode.' - '.$title;
		}

		return $options;
	}

	public function suggestionType()
	{
		return [
			'' => trans('front/suggestions.selectType'),
			'suggestion' => trans('front/suggestions.suggestion'),
			'complaint' => trans('front/suggestions.complaint'),
			'question' => trans('front/suggestions.question'),
		];
	}

	public function idTitlePartManufacturers($items)
	{
		$options=['' => trans('front/parts.selectManufacturer')];		
		foreach ($items as $item) {
			$options[$item->id]=$item->title;
		}
		return $options;
	}

	public function productGroups($items)
	{
		$options=['' => trans('front/parts.selectGroup')];		
		foreach ($items as $item) {
			$options[$item->product_group]=$item->product_group;
		}
		return $options;
	}

	public function fuels($items)
	{
		$options=['' => trans('front/parts.selectFuel')];		
		foreach ($items as $item) {
			$options[$item->fuel]=$item->fuel;
		}
		return $options;
	}

}