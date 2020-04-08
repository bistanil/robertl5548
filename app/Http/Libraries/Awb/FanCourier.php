<?php

namespace App\Http\Libraries\Awb;
use App;
use Auth;
use App\Models\Client;
use App\Models\DeliveryAddress;
use App\Models\County;
use App\Models\City;
use App\Models\Awb;
use App\Models\Order;
use App\Models\Company;
use App\Models\AwbWeight;
use App\Models\AwbContactPerson;
use App\Models\AwbDetail;
use App\Models\AwbCredential;
use Mail;
use Excel;
use Storage;

Class FanCourier {

	protected $request;
	protected $order;	

	public function __construct($request,$order)
	{
		$this->request = $request;
		$this->order = $order;
	} 

	public function create()
	{
		$company = Company::whereDefault('yes')->get()->first();
		$dimension = AwbWeight::find($this->request->dimension_id);
		$contactPerson = AwbContactPerson::find($this->request->contact_person_id);
		$awb = new Awb();
		$awb->order_id = $this->order->id;
		$awb->service_type = $this->request->service_type;
		if($this->request->service_type == 'collectorAccount') {
			$awb->bank = $company->bank; 
			$awb->bank_account =  $company->bank_account;			
		} else {
			$awb->bank = '';
			$awb->bank_account =  '';				
		}
		$awb->envelopes = '';
		$awb->packages = $this->request->packages;
		$awb->weight = $dimension->weight;
		$awb->expedition_payment = $this->request->expedition_payment;
		$awb->cash_on_delivery = $this->request->cash_on_delivery;
		if($this->request->cash_on_delivery != '') $awb->cash_on_delivery_payment_at = 'recipient'; 
		else $awb->cash_on_delivery_payment_at = '';
		$awb->contact_person_sender = $contactPerson->name.' - '.$contactPerson->phone;
		$options = implode(' / ',$this->request->comments);
		$awb->comments = $options; 
		if($this->order->client_company_title != '') $awb->nume_destinatar = $this->order->client_company_title;
		else $awb->recipient_name = $this->order->client_name;
		$awb->recipient_contact_person = $this->order->client_name;
		$content = [];
		foreach($this->order->items as $key=>$item)
		{
			$content[] = $item->title;
		}
		$awb->content = '';
		$awb->recipient_phone = $this->order->client_phone;
		$awb->recipient_email = $this->order->client_email;
		$awb->recipient_county = $this->request->recipient_county;
		$awb->recipient_city = $this->request->recipient_county;

		$awb->recipient_street = $this->request->recipient_street;
		$awb->recipient_street_no = '';
		$awb->recipient_postal_code = $this->request->recipient_postal_code;
		$awb->recipient_block = '';
		$awb->recipient_scale = '';
		$awb->recipient_floor = '';
		$awb->recipient_apartment = '';

		$awb->package_height = $dimension->height;
		$awb->package_width = $dimension->width;
		$awb->package_length = $dimension->lenght;
		

		$awb->options = $this->request->options;
		$awb->dimension_id = $this->request->dimension_id;
		
		$oldAwb = Awb::whereOrder_id($this->order->id)->get()->first();
		if($oldAwb != null) {
			$oldAwb->update($awb->toArray());
			$awb = $oldAwb;
			$this->deleteAwb($this->order->id);
		} else {
			$awb->save();
		}	
        $this->sendAwb($this->order->id, $awb->id);
		return $awb;
	}

	 public function sendAwb($id,$awbId)
    {
      $order = Order::find($id);
      $credential = AwbCredential::whereType('fanCourier')->get()->first();
      $awb = Awb::find($awbId); 
      $excel = App::make('excel');
      Excel::create(trans('admin/awbs.awb').'-'.$order->id, function($excel) use ($awb) {
            $excel->sheet(trans('admin/awbs.awb'), function($sheet) use ($awb) {
                $sheet->loadView('admin.partials.awbs.excel')
                      ->with('awb', $awb);
            });
      })->store('csv', public_path('files/awbs'));
      $path = curl_file_create(realpath('public/files/awbs/awb-'.$order->id.'.csv'));
      $postArr = array('fisier' => $path, 'username' => $credential->username, 'client_id' => $credential->client_id, 'user_pass' => $credential->user_pass);
      $ch = curl_init('https://www.selfawb.ro/import_awb_integrat.php'); 
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postArr);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FAILONERROR,true);
      curl_setopt(($ch), CURLOPT_VERBOSE , true);      
      curl_setopt($ch,CURLINFO_HEADER_OUT,true);      
      $postResult = curl_exec($ch);
      curl_close($ch);      
      $awb = explode(',',$postResult);
      AwbDetail::whereOrder_id($order->id)->delete();
      $awbDetail = new AwbDetail();
      $awbDetail->order_id = $order->id;
      //dd($awb);
      $awbDetail->awb_number = $awb[2];
      $awbDetail->save();

    }

    public function deleteAwb($id)
    {
    	$order = Order::find($id);
    	$credential = AwbCredential::whereType('fanCourier')->get()->first();
    	if($order->awbDetail != null) {
	    	$ch = curl_init('http://www.selfawb.ro/delete_awb_integrat.php'); 
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));
		    curl_setopt($ch, CURLOPT_POSTFIELDS, array('AWB'=> $order->awbDetail->awb_number,'username' => $credential->username, 'client_id' => $credential->client_id, 'user_pass' => $credential->user_pass ));
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch,CURLOPT_FAILONERROR,true);
		    $postResult = curl_exec($ch);
		    curl_close($ch);
	    	AwbDetail::whereOrder_id($order->id)->delete();
    	}
    }


}