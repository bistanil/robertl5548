<?php

namespace App\Http\Libraries;

use App;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\DeliveryAddress;
use App\Models\County;
use App\Models\City;

Class DeliveryAddressManager{

	protected $request;
	protected $client;

	public function __construct(Request $request, Client $client)
	{
		$this->request = $request;
		$this->client = $client;
	}

	public function get()
	{
		$address = $this->getById();
		if ($address != null) return $address;
		return $this->getFromRequest();
	}

	private function getById()
	{
		return DeliveryAddress::find($this->request->client_address_id);
	}

	private function getFromRequest()
	{
		$address = DeliveryAddress::whereClient_id($this->client->id)
								  ->whereAddress($this->request->delivery_address)
								  ->whereName($this->request->contact_person_name)
								  ->wherePhone($this->request->contact_person_phone)
								  ->whereCounty($this->request->county)
								  ->whereCity($this->request->city)
								  ->wherePostal_code($this->request->postal_code)
								  ->get()
								  ->first();
		if ($address != null) return $address;
		return $this->create();
	}

	private function create()
	{
		$county = County::find($this->request->county_id);
		$city = City::find($this->request->city_id);
		$address = new DeliveryAddress();
		$address->address = $this->request->delivery_address;
		$address->name = $this->request->contact_person_name;
		$address->phone = $this->request->contact_person_phone;
		$address->county = $county->title;
		$address->city = $city->title;
		$address->postal_code = $this->request->client_delivery_postal_code;
		$address->client_id = $this->client->id;
		$address->save();
		return $address;
	}
}