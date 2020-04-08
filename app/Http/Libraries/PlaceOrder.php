<?php

namespace App\Http\Libraries;
use App;
use Cart;
use Notification;
use Storage;
use App\Models\Order;
use App\Models\Client;
use App\Models\Discount;
use App\Models\Company;
use App\Models\ClientCompany;
use App\Models\TransportMargin;
use App\Models\Currency;
use App\Models\DeliveryAddress;
use App\Models\ProductPrice;
use App\Models\SettingsEmail;
use App\Models\ClientCar;
use App\Http\Libraries\CartDiscounter;
use App\Http\Libraries\OrderItems;
use App\Notifications\ClientOrderSent;
use App\Notifications\AdminOrderReceived;
use App\Notifications\ClientOrderUpdated;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Messages\MailMessage;
use MobilPay;

Class PlaceOrder{

	protected $client;
	protected $request;
	protected $instance;
	protected $currency;	

	public function create($request, Client $client, $instance = 'default')
	{
		$this->client = $client;				
		$this->request = $request;
		$this->instance = $instance;
		$this->currency = $this->setCurrency();
		$this->saveOrder();				
		return TRUE;
	}

	private function saveOrder()
	{	
		$transport = $this->transport();
		if (isset($this->request->order_id)) $order = Order::find($this->request->order_id);
		else $order = new Order();
		$order->currency = $this->currency->code;
		$order->language = $this->language();
		$order->status = 'new';
		$order->intern_status = '';
		$order->vin = $this->request->vin;
		$order->transport_type = $this->request->transport_type;	
		$order->payment_method = $this->request->payment_method;	
		$order->observations = $this->request->observations;
		$order->accept_policy = $this->request->accept_policy;
		$order->accept_terms = $this->request->accept_terms;
		$order = $this->setOrderCarInfo($order);	
		$order = $this->setOrderClientInfo($order);
		$order = $this->setOrderClientCompanyInfo($order);		
		$order = $this->setOrderDeliveryAddress($order);
		$order = $this->setOrderCompanyInfo($order);				
		$cartDiscounter = new CartDiscounter($this->request, $this->instance, $this->client);		
		$order->discount_amount = $cartDiscounter->cartDiscountValue();
		$order->transport_cost = $transport->margin;
		$order->total = $cartDiscounter->discountedTotal() + $transport->margin;
		$order->save();
        $orderItems = new OrderItems($this->request, $order, $this->currency, $this->instance, $this->client);        
        $orderItems->saveItems();
        Cart::instance($this->instance)->destroy();
        if ($this->instance == 'default') session()->forget('editOrderId');
        ProductPrice::whereSource('temporary')->delete();
        if (isset($this->request->order_id))
        {
        	//client email order edited by admin
	        Notification::send($order, new ClientOrderUpdated($order));
	        Storage::disk('proforma')->delete('proforma'.$order->id.'.pdf');
        } else {
        	//mobilpay
        	if ($order->payment_method == 'mobilpay') MobilPay::purchase($order);
	        //client email new order
	        Notification::send($order, new ClientOrderSent($order));
	        Storage::disk('proforma')->delete('proforma'.$order->id.'.pdf');
	        //admin email new order
	        $adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
	        Notification::send($adminEmail, new AdminOrderReceived($order)); 
	        Storage::disk('proforma')->delete('proforma'.$order->id.'.pdf');
    	}
	}

	private function setOrderClientInfo($order)
	{
		$order->client_id = $this->client->id;
		$order->client_name = $this->client->name;
		$order->client_email = $this->client->email;
		$order->client_phone = $this->client->phone;
		$order->client_gender = $this->client->gender;
		return $order;
	}

	private function setOrderClientCompanyInfo($order)
	{
		$clientCompany = $this->clientCompany();
		if ($clientCompany != null)
		{
			$order->client_company_title = $clientCompany->title;
			$order->client_company_fiscal_code = $clientCompany->fiscal_code;
			$order->client_company_registration_number = $clientCompany->registration_number;
			$order->client_company_address = $clientCompany->address;
			$order->client_company_bank = $clientCompany->bank;
			$order->client_company_bank_account = $clientCompany->bank_account;
		}
		return $order;
	}

	private function setOrderDeliveryAddress($order)
	{		
		$deliveryAddress = $this->deliveryAddress();
		if ($deliveryAddress != null)
		{
			$order->client_delivery_address = $deliveryAddress->address;
			$order->client_delivery_contact_person = $deliveryAddress->name;
			$order->client_delivery_phone = $deliveryAddress->phone;
			$order->client_delivery_county = $deliveryAddress->county;
			$order->client_delivery_city = $deliveryAddress->city;
			$order->client_delivery_postal_code = $deliveryAddress->postal_code;
		}		
		return $order;
	}

	private function setOrderCarInfo($order)
	{
		$type = session()->get('type');
		if ($type == null) {
			$order->car_info = null;
			return $order;
		}
		$order = $this->setOrderVin($order);
		$order->car_info = $type->model->modelsGroup->car->title.' '.$type->model->title.' '.$type->title.' ('.$type->kw.' '.trans('front/cars.kw').' / '.$type->hp.' '.trans('front/cars.hp').')'.' ('.$type->construction_start_year.' - '.$type->construction_end_year.') - '.$type->fuel;
		//$order->car_info = $type->model->modelsGroup->car->title.' '.$type->model->title.' '.$type->title.' '.$type->construction_start_year.' - '.$type->construction_end_year.' '.$type->kw.' '.trans('front/cars.kw').' '.$type->fuel;
		return $order;
	}

	private function setOrderVin($order)
	{
		$car = ClientCar::whereClient_id($this->client->id)->whereType_id(session()->get('type')->id)->get()->first();
		if ($car == null) return $order;
		if($car->vin != null) $order->vin = $car->vin;
		else $order->vin = $this->request->vin;
		return $order;
	}

	private function setOrderCompanyInfo($order)
	{
		$company = $this->getCompany();
		$order->company_id = $company->id;		
		$order->company_title = $company->title;
		$order->company_vat_code = $company->vat_code;
		$order->company_registration_code = $company->registration_code;
		$order->company_address = $company->address;
		$order->company_bank = $company->bank;
		$order->company_bank_account = $company->bank_account;
		return $order;
	}

	private function applyDiscount($discountId = false)
	{
		$discount = Discount::find($discountId);
		$total = cartTotal($this->instance);
		if ($discount != null) $total = $total * $discount->discount;
		if($this->client->discounts != null) $total = $total * $this->client->discounts()->get()->first()->discount;
		return $total;
	}

	private function getCompany()
	{
		if (isset($this->request->company_id)){
			$company = Company::find($this->request->company_id);	
			if ($company != null) return $company;
		} 
		return Company::whereDefault('yes')->get()->first();
	}

	private function clientCompany()
	{
		if (isset($this->request->client_company_id)){
			return ClientCompany::find($this->request->client_company_id);
		}
	}

	private function transport()
	{
		$transport = TransportMargin::where('min', '<=', Cart::instance($this->instance)->subtotal())->where('max', '>=', Cart::instance($this->instance)->subtotal())->where('type_id',$this->request->transport_type)->get()->first();
		if ($transport != null) return $transport;
		return (object) ['margin' => 0];
	}

	private function setCurrency()
	{		
		if ($this->request->has('currency')) return $this->currency = Currency::find($this->request->currency);
		return $this->currency = Currency::whereDefault('yes')->get()->first();
	}

	private function language()
	{
		if (isset($request->language)) return $request->language;
		else return App::getLocale();
	}

	private function deliveryAddress()
	{
		return DeliveryAddress::find($this->request->client_address_id);
	}

}