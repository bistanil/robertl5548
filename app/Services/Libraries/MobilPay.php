<?php namespace App\Services\Libraries;

use App;
use App\Models\PaymentGateway;
use App\Models\Payment;
use Omnipay\Omnipay;
use Carbon\Carbon;

class MobilPay{

	protected $merchantId;
	protected $publicKey;
	protected $privateKey;
	protected $testMode;
	protected $gateway;	

	public function __construct()
	{
		$this->setCredentials();
		$this->createGateway();
	}

	private function setCredentials()
	{
		$gateway = PaymentGateway::whereType('mobilpay')->get()->first();
		$this->merchantId = $gateway->username;
		$this->publicKey = $gateway->public_key;
		$this->privateKey = $gateway->private_key;
		if ($gateway->test == 'yes') $this->testMode = true;
		else $this->testMode = false;
	}

	private function createGateway()
	{
		$this->gateway = Omnipay::create('MobilPay');
		$this->gateway->setMerchantId($this->merchantId);
		$this->gateway->setPublicKey(storage_path('gateways/'.$this->publicKey));
		$this->gateway->setPrivateKey(storage_path('gateways/'.$this->privateKey));
	}

	public function purchase($order)
	{
		$payment = $this->savePayment($order);
		$response = $this->gateway->purchase([
			    'amount'     => $order->total,
			    'currency'   => 'RON',
			    'orderId'    => $payment->id,
			    'confirmUrl' => route('front-mobilpay-response'),
			    'returnUrl'  => route('thank-you-page'),
			    'details'    => 'Comanda nr.'.$order->id.' plasata pe garageauto.ro',
			    'testMode'   => $this->testMode,
			    'params'     => [
			        'order_id' => $order->id
			    ]
			])->send();

			$response->redirect();
	}

	private function savePayment($order)
	{
		$payment = new Payment();
		$payment->started_at = new Carbon();
		$payment->order_id = $order->id;
		$payment->name = $order->client_name;
		$payment->email = $order->client_email;
		$payment->company = $order->client_company_title;
		$payment->amount = $order->total;
		$payment->currency = $order->currency;
		$payment->gateway = 'MobilPay';
		$payment->save();
		return $payment;
	}

	public function processResponse($response)
	{
		$response = $this->gateway->completePurchase($_POST)->send();
		$responseData = $response->getData();		
		$response->sendResponse();

		$payment = Payment::find($responseData['orderId']);
		$payment->response_time = new Carbon();
		switch($response->getMessage())
		{
		    case 'confirmed_pending': // transaction is pending review. After this is done, a new IPN request will be sent with either confirmation or cancellation
		        $payment->response = 'confirmed_pending';
		        break;
		    case 'paid_pending': // transaction is pending review. After this is done, a new IPN request will be sent with either confirmation or cancellation
		        $payment->response = 'paid_pending';
		        break;
		    case 'paid': // transaction is pending authorization. After this is done, a new IPN request will be sent with either confirmation or cancellation
				$payment->response = 'paid';	        
		        break;
		    case 'confirmed': // transaction is finalized, the money have been captured from the customer's account
		        $payment->response = 'confirmed';
		        break;
		    case 'canceled': // transaction is canceled
		        $payment->response = 'canceled';
		        break;
		    case 'credit': // transaction has been refunded
		        $payment->response = 'credit';
		        break;
		}
		$payment->save();		
	}
}