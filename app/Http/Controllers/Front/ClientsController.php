<?php namespace App\Http\Controllers\Front;

use App;
use Auth;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use JavaScript;
use Carbon\Carbon;
use DB;
use URL;
use App\Http\Libraries\Meta;
use App\Http\Libraries\ClientWishlist;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\Warranty;
use App\Models\Page;
use App\Http\Requests\Front\ClientRequest;

class ClientsController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

    public function index()
	{	
		if (session()->has('redirectToCheckout')) if (session()->get('redirectToCheckout') == 'yes') return redirect(route('front-checkout'));
		$client = Auth::guard('client')->user();
		$orders = $client->orders()->orderBy('id', 'desc')->limit(5)->get();
		$companies = $client->companies()->limit(5)->get();
		$reviews = $client->reviews()->limit(5)->get();
		$addresses = $client->deliveryAddresses()->limit(5)->get();
		$meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientAccount';        
        return view('front.partials.clients.account.account', compact('meta', 'breadcrumb', 'orders', 'addresses', 'companies', 'reviews', 'client','addresses'));
	}

	public function edit()
	{
		$client = Auth::guard('client')->user();
		$meta = Meta::build('ClientAccount');
		$breadcrumb = 'frontClientEditAccount';
		return view('front.partials.clients.account.form', compact('meta', 'breadcrumb', 'client'));
	}

	public function update(Client $client, ClientRequest $request)
	{
		$client=Auth::guard('client')->user();
		$client->slug=str_slug($request->name.'-'.$request->email, "-");
        if (!empty($request->password)) {
            if ($request->password === $request->password_confirmation) {
                $client->password = bcrypt($request->password);
                $client->save();                
            }
        }
        if ($client->update($request->except('password','password_confirmation'))) frontFlash()->success(trans('front/clients.editAccountFlashTitle'), trans('front/clients.editAccountSuccessText'));
        else frontFlash()->error(trans('front/clients.editAccountFlashTitle'), trans('front/clients.editAccountErrorText'));
        return redirect(route('client-account'));
	}

	public function invoices()
	{	
		$client = Auth::guard('client')->user();
		$invoices = OrderInvoice::orderBy('created_at', 'desc')->get();
		$breadcrumb = 'frontClientAccount'; 
		$meta = Meta::build('ClientAccount');       
        return view('front.partials.clients.invoices.main', compact('meta', 'breadcrumb', 'invoices', 'meta'));
	}

	public function warranties()
	{	
		$client = Auth::guard('client')->user();
		$warranties = Warranty::orderBy('start_date', 'desc')->get();
		$pages = Page::whereActive('active')->whereLanguage(App::getLocale())->whereMenu('warranties')->get();
		$breadcrumb = 'frontClientAccount'; 
		$meta = Meta::build('ClientAccount');       
        return view('front.partials.clients.warranties.main', compact('meta', 'breadcrumb', 'warranties', 'meta', 'pages'));
	}

}