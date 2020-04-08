<?php

namespace App\Http\Libraries;

use App;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientCompany;

Class ClientCompanyManager{

	protected $client;
	protected $request;

	public function __construct(Request $request, Client $client)
	{
		$this->request = $request;
		$this->client = $client;
	}

	public function get()
	{
		$company = $this->getById();
		if ($company != null) return $company;
		return $this->getFromRequest();
	}

	private function getById()
	{
		return ClientCompany::find($this->request->client_company_id);
	}

	private function getFromRequest()
	{
		$company = ClientCompany::whereClient_id($this->client->id)->whereFiscal_code($this->request->fiscal_code)->get()->first();
		if ($company != null) return $this->updateCompany($company);
		return $this->create();
	}

	private function updateCompany($company)
	{
		$company = $this->setAttributes($company);
		$company->save();
		return $company;
	}

	private function create()
	{
		$company = new ClientCompany();
		$company = $this->setAttributes($company);
		$company->save();
		return $company;
	}

	private function setAttributes($company)
	{
		$company->client_id = $this->client->id;
		$company->title = $this->request->company_title;
		$company->fiscal_code = $this->request->fiscal_code;
		$company->registration_number = $this->request->registration_number;
		$company->bank = $this->request->bank;
		$company->bank_account = $this->request->bank_account;
		$company->address = $this->request->company_address;
		return $company;
	}


}