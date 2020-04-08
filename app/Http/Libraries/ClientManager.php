<?php

namespace App\Http\Libraries;

use App;
use Auth;
use Mail;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\EmailTemplate;
use App\Models\SettingsEmail;
use App\Models\Newsletter;
use Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\ClientRegisterMessage;
use App\Notifications\AdminClientRegisterMessage;
use Illuminate\Notifications\Notifiable;

Class ClientManager{
	
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function get()
	{
		$client = $this->getClientById();
		if ($client != null) return $client;
		$client = $this->getClientFromAuth();
		if ($client != null) return $client;			
		$client = $this->getClientByEmail();
		if ($client != null) return $this->updateInfo($client);
		return $this->create();
	}

	private function getClientById()
	{
		return Client::find($this->request->client_id);
	}

	private function getClientFromAuth()
	{
		if (Auth::guard('client')->check())  return Auth::guard('client')->user();
		return null;
	}

	private function getClientByEmail()
	{
		return Client::whereEmail($this->request->email)->get()->first();
	}

	private function create()
	{
		$client = new Client($this->request->all());
        $client->password = bcrypt($client->password);
        $client->slug=str_slug($client->name.'-'.$client->email, "-");
        $client->origin = 'admin';
        $client->active = 'active';
        if ($this->request->account == 'newAccount') $client->origin = 'client';
        $client->save();        
        //$this->addToNewsletter();
        if ($client->origin == 'client') 
        {
        	$this->notifyClient($client);
        	$this->notifyAdmin($client);
        	Auth::guard('client')->login($client, true);
        }
        return $client;
	}

	private function addToNewsletter()
	{
		if ($this->request->email != null)
		{
			$registration = new Newsletter();
			$registration->news_email = $this->request->email;
			$registration->news_name = $this->request->name;
			$registration->active = 'active';
			$registration->save();
		}
	}

	private function notifyClient($client)
	{
		Notification::send($client, new ClientRegisterMessage($client));
	}

	private function notifyAdmin($client)
	{
		$adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
		Notification::send($adminEmail, new AdminClientRegisterMessage($client));
	}

	public function updateInfo($client)
	{
		if ($client->origin == 'client' && $this->request->account == 'newAccount') 
		{
			frontFlash()->info(trans('front/clients.accountExistsTitle'), trans('front/clients.accountExistsText'));
			return null;
		}
		if ($this->request->account == 'newAccount')
		{
			$client->password = bcrypt($this->request->password);
			$client->origin = 'client';
			$this->notifyClient($client);
			Auth::guard('client')->login($client, true);
		}
		$client->gender = $this->request->gender;
		$client->name = $this->request->name;
		$client->phone = $this->request->phone;
		$client->save();
		return $client;		
	}

}