<?php namespace App\Http\Controllers\Front;

use App;
use Auth;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use URL;
use JavaScript;
use Carbon\Carbon;
use DB;
use App\Http\Libraries\Meta;
use App\Models\Page;
use App\Models\Slide;
use App\Models\Contact;
use App\Models\Car;
use App\Models\SettingsEmail;
use App\Models\ContactMessage;
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Models\OfferRequest;
use App\Models\ReturnedProduct;
use App\Models\OrderItem;
use App\Models\Career;
use App\Models\Suggestion;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\Front\ContactRequest;
use App\Http\Requests\Front\SuggestionRequest;
use App\Http\Requests\Front\OfferRequestRequest;
use App\Http\Requests\Front\ReturnProductRequest;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\SendContactMessage;
use App\Notifications\SendSuggestionMessage;
use App\Notifications\SendRequestOfferMessage;
use App\Notifications\SendReturnProductMessage;
use App\Http\Requests\Front\ClientDeleteRequestRequest;
use App\Notifications\SendClientDeleteRequest;
use App\Models\ClientDeleteRequest;
use Mail;
use Notification;
use Cart;

class FormsController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

	public function index()
	{			
		
		 
    }

	public function requestOffer()
	{			
		$breadcrumb='frontRequestOffer';
		$meta = Meta::build('requestOffer');
		$contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first();
		$cars = Car::whereActive('active')->whereLanguage(App::getLocale())->get();
		if (session()->has('type'))
		{
			$models = CarModel::whereModel_group_id(session()->get('type')->model->modelsGroup->id)->whereActive('active')->get();
			$types = CarModelType::whereModel_id(session()->get('type')->model->id)->whereActive('active')->get();
            $fuels = CarModelType::select('fuel')->distinct()->whereModel_id(session()->get('type')->model_id)->whereActive('active')->get();
		} else {
			$models = [];
			$types = [];
            $fuels = [];
		}
		if (session()->has('part'))
		{
			$part = session()->get('part');
			$partInfo = $part->manufacturer->title.' '.$part->title.' '.$part->code;
			session()->forget('part');
		} else $partInfo = '';
		return view('front.partials.pages.requestOffer', compact('meta','breadcrumb', 'cars', 'models', 'types', 'partInfo', 'contactInfo','fuels'));
	}

	public function sendOfferRequest(OfferRequestRequest $request)
	{
		$adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
		if ($request->has('type_id')) $type = CarModelType::find($request->type_id);
		else $type = null;
		$message = new OfferRequest($request->all());
        $message->language = App::getLocale();
        $message->status = 'new';
        $message->second_status = 'new';
        Notification::send($adminEmail, new SendRequestOfferMessage($message, $type)); 
        if ($message->save()) frontFlash()->success(trans('front/common.sendFlashTitle'), trans('front/common.sendSuccessText'));
        else frontFlash()->error(trans('front/common.sendFlashTitle'), trans('front/common.sendErrorText'));
        return redirect(route('front-request-offer'));
	}

	public function contact()
	{			
		$contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first();
		$breadcrumb ='frontContact';
		$meta = Meta::build('contact');
		return view('front.partials.pages.contact', compact('meta','breadcrumb','contactInfo'));
	}

	public function sendContactMessage(ContactRequest $request)
	{
		$adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
		$message = new ContactMessage($request->all());
        $message->language = App::getLocale();
        $message->status = 'new';
        $message->second_status = 'new';
        Notification::send($adminEmail, new SendContactMessage($message));  
        if ($message->save()) frontFlash()->success(trans('front/common.sendFlashTitle'), trans('front/common.sendSuccessText'));
        else frontFlash()->error(trans('front/common.sendFlashTitle'), trans('front/common.sendErrorText'));
        return redirect(route('front-contact'));
	}

	public function suggestions()
	{			
		$contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first();
		$breadcrumb ='frontSuggestions';
		$meta = Meta::build('suggestions');
		return view('front.partials.pages.suggestions', compact('meta','breadcrumb','contactInfo'));
	}

	public function sendSuggestionMessage(SuggestionRequest $request)
	{
		$adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
		$message = new Suggestion($request->all());
        $message->language = App::getLocale();
        $message->status = 'new';
        Notification::send($adminEmail, new SendSuggestionMessage($message));  
        if ($message->save()) frontFlash()->success(trans('front/common.sendFlashTitle'), trans('front/common.sendSuccessText'));
        else frontFlash()->error(trans('front/common.sendFlashTitle'), trans('front/common.sendErrorText'));
        return redirect(route('front-suggestions'));
	}

	public function showSuggestionMessage(Request $request)
    {
    	if($request->returnValue == 'suggestion') {
    		echo trans('front/suggestions.suggestionText');
    	} elseif($request->returnValue == 'complaint') {
    		echo trans('front/suggestions.complaintText');
    	} elseif($request->returnValue == 'question') {
    		echo trans('front/suggestions.questionText');
    	} else {

    	}
    }

	public function returnProduct(Request $request)
    {
        $client = Auth::guard('client')->user();
        if($client) {
    		$orders = $client->orders()->whereStatus('received')->where(DB::raw('date_add(received_at, interval 30 day)'), '>=', DB::raw('current_timestamp()'))->orderBy('id', 'desc')->get();
            $order = '';
    		$codes = '';			
		} else {
			$orders = array();
			$order = '';
			$codes = '';
		}
		$contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first();
        $page = Page::whereActive('active')->whereMenu('return')->whereLanguage(App::getLocale())->get()->first();
        $returns = Page::whereActive('active')->whereLanguage(App::getLocale())->whereMenu('return')->get();
        $breadcrumb = 'frontReturnProduct';
    	$meta = Meta::build('returnProduct');
    	return view('front.partials.clients.returnProduct', compact('meta','breadcrumb','orders', 'order', 'codes', 'page', 'date', 'value','contactInfo','returns'));
    }

	public function sendReturnProductMessage(ReturnProductRequest $request)
    {
        $message = new ReturnedProduct($request->all());
        $adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();		
		if(Auth::guard('client')->check()) {
    		$message->client_id = Auth::guard('client')->user()->id;
    		$message->product_codes = implode('|', array_values($request->product_codes));
     	} else {
     		$message->client_id = '';
    	}
        $message->status = 'new';
        Notification::send($adminEmail, new SendReturnProductMessage($message));  
        if ($message->save()) frontFlash()->success(trans('front/common.sendFlashTitle'), trans('front/common.sendSuccessText'));
        else frontFlash()->error(trans('front/common.sendFlashTitle'), trans('front/common.sendErrorText'));
        return redirect(route('front-return-product'));
    }

    public function productCodesList(Request $request)
    {
    	$codes = OrderItem::whereOrder_id($request->orderId)->get();
    	return view('front.partials.clients.codesList', compact('codes'));
    }

    public function showMessage(Request $request)
    {
    	if($request->returnValue == 'cash') {
    		echo trans('front/returnedProducts.returnBackCash');
    	} elseif($request->returnValue == 'anotherProduct') {
    		echo trans('front/returnedProducts.returnBackAnotherProduct');
    	} else {

    	}
    }

    public function withdrawals()
    {           
        $contactInfo = Contact::whereLanguage(App::getLocale())->get()->first();
        $page = Page::whereActive('active')->whereLanguage(App::getLocale())->whereMenu('withdrawal')->get()->first();
        $breadcrumb = 'frontPages';
        if ($page == null)
        { 
            $breadcrumb='frontHome';
            $meta = Meta::build('home');
        }
        $meta = Meta::build(null, $page);
        $item = $page; 
        return view('front.partials.clients.withdrawals', compact('meta','breadcrumb','contactInfo', 'pages', 'page', 'item'));
    }

    public function sendWithdrawalMessage(ClientDeleteRequestRequest $request)
    {
        $adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
        $message = new ClientDeleteRequest($request->all());
        $client = Auth::guard('client')->user();
        if($client) $message->client_id = $client->id;
        $message->status = 'new';
        $message->second_status = 'new';
        Notification::send($adminEmail, new SendClientDeleteRequest($message));  
        if ($message->save()) frontFlash()->success(trans('front/common.sendFlashTitle'), trans('front/common.sendSuccessText'));
        else frontFlash()->error(trans('front/common.sendFlashTitle'), trans('front/common.sendErrorText'));
        return redirect(route('front-withdrawals'));
    }

}