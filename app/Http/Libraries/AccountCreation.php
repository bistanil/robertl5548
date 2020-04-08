<?php

namespace App\Http\Libraries;
use App;
use App\Http\Libraries\ClientManager;
use App\Http\Libraries\ClientCompanyManager;
use App\Http\Libraries\DeliveryAddressManager;
use App\Http\Libraries\PlaceOrder;

Class AccountCreation{

	protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function create($instance = 'shopping')
	{
		$client = new ClientManager($this->request);
		$client = $client->get();
		if ($client == null) return null;		
		$orderInfo = collect([]);
		if ($this->request->invoiceTo == 'company')
		{
			$company = new ClientCompanyManager($this->request, $client);
			$company = $company->get();
			$orderInfo->client_company_id = $company->id;
		}
		if ($this->request->require_delivery_address == 1)
		{
			$deliveryAddress = new DeliveryAddressManager($this->request, $client);				
			$deliveryAddress = $deliveryAddress->get();
			$orderInfo->client_address_id = $deliveryAddress->id;
		} else $orderInfo->client_address_id = 0;
		$orderInfo->transport_type = $this->request->transport_type;
		$orderInfo->payment_method = $this->request->payment_method;
		$orderInfo->vin = $this->request->vin;
		$orderInfo->observations = $this->request->observations;
		$orderInfo->accept_policy = $this->request->accept_policy;
		$orderInfo->accept_terms = $this->request->accept_terms;
		if ($this->request->has('order_id')) $orderInfo->order_id = $this->request->order_id;
		$order = new PlaceOrder();
		return $order->create($orderInfo, $client, $instance);
	}

}