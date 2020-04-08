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
use App\Http\Libraries\AccountCreation;
use App\Models\Client;
use App\Models\Page;
use App\Models\Order;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\TransportMargin;
use App\Models\TransportType;
use App\Models\Logo;
use App\Models\Currency;
use App\Models\Contact;
use App\Models\Company;
use App\Models\PaymentGateway;
use App\Http\Requests\Front\NoAccountOrderRequest;
use App\Http\Requests\Front\AccountOrderRequest;
use App\Http\Requests\Front\OrderRequest;
use App\Notifications\ClientOrderUpdated;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Http\Request;
use App\Models\County;
use App\Models\City;
use Cart;
use MobilPay;

class OrdersController extends Controller {

    public function __construct()
    {       
        JavaScript::put(['baseUrl' => URL::to('/')]);       
    }

    public function index()
    {   
        $client = Auth::guard('client')->user();
        $orders = Order::whereClient_id($client->id)->orderBy('created_at', 'desc')->paginate();
        $meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientOrders';        
        return view('front.partials.clients.orders.orders', compact('meta', 'breadcrumb', 'client', 'orders'));
    }

    public function show($id, Order $order)
    {
        $order = $order->find($id);
        $logo = Logo::whereActive('active')->whereLanguage(App::getLocale())->whereType('proforma')->get()->first();
        $meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientOrder';        
        return view('front.partials.clients.orders.order', compact('meta', 'breadcrumb', 'order', 'logo'));
    }

    public function checkout(Request $request)
    {
        if (Cart::instance('shopping')->count() == 0) {
            frontFlash()->error(trans('front/orders.sendFlashTitle'), trans('front/cart.noProduct'));
            return redirect(route('front-cart'));
        }
        if (!Auth::guard('client')->check()) return redirect(route('front-checkout-no-account'));
        if($request != null) {
            $oldCounty = County::find($request->old('county_id'));
            if($oldCounty != null) $cities = $oldCounty->cities()->orderBy('title')->get();
            else $cities = [];
            $oldTransport = $request->old('transport_type');
            $transport = TransportMargin::where('min', '<=', convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal()))->where('max', '>=', convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal()))->where('type_id',$oldTransport)->get()->first();
        } else {
            $cities = [];
            $transport = '';
        }
        $counties = County::whereActive('active')->orderBy('title','ASC')->get();
        $catalogProduct = new CatalogProduct();
        $productPrice = new ProductPrice();
        $paymentMethods = PaymentGateway::whereActive('active')->sorted()->get();
        $meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientOrdersCreate';
        return view('front.partials.clients.orders.form', compact('meta', 'breadcrumb', 'catalogProduct', 'productPrice', 'transport', 'counties', 'cities','paymentMethods'));
    }

    public function checkoutNoAccount(Request $request)
    {
        if (Cart::instance('shopping')->count() == 0) {
            frontFlash()->error(trans('front/orders.sendFlashTitle'), trans('front/cart.noProduct'));
            return redirect(route('front-cart'));
        }
        session()->put('redirectToCheckout', 'yes');
        if($request != null) {
            $oldTransport = $request->old('transport_type');
            $oldCounty = County::find($request->old('county_id'));
            if($oldCounty != null) $cities = $oldCounty->cities()->orderBy('title')->get();
            else $cities = [];
            $transport = TransportMargin::where('min', '<=', convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal()))->where('max', '>=', convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal()))->where('type_id',$oldTransport)->get()->first();
        } else {
            $cities = [];
            $transport = '';
        }
        $counties = County::whereActive('active')->orderBy('title','ASC')->get();
        $catalogProduct = new CatalogProduct();
        $productPrice = new ProductPrice();
        $paymentMethods = PaymentGateway::whereActive('active')->sorted()->get();
        $meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientOrdersCreate';
        return view('front.partials.clients.orders.formNoAccount', compact('meta', 'breadcrumb', 'catalogProduct', 'productPrice', 'transport', 'counties', 'cities','paymentMethods'));
    }

    public function storeNoAccount(NoAccountOrderRequest $request)
    {
        $client = new AccountCreation($request);
        $result = $client->create();
        if ($result == null) return redirect()->route('client-login');      
        if ($result == true) frontFlash()->success(trans('front/orders.sendFlashTitle'), trans('front/orders.sendSuccessText'));
        else frontFlash()->error(trans('front/orders.sendFlashTitle'), trans('front/orders.sendErrorText'));
        return redirect(route('thank-you-page'));
    }

    public function store(AccountOrderRequest $request)
    {
        $client = new AccountCreation($request);
        $result = $client->create();        
        if ($result == true) frontFlash()->success(trans('front/orders.sendFlashTitle'), trans('front/orders.sendSuccessText'));
        else frontFlash()->error(trans('front/orders.sendFlashTitle'), trans('front/orders.sendErrorText'));
        return redirect(route('thank-you-page'));
    }

    public function transportCost(Request $request)
    {
        $currency = Currency::defaultCurrency();
        $transport = TransportMargin::where('min', '<=', convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal()))->where('max', '>=', convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal()))->where('type_id',$request->transportId)->get()->first();
        if($transport != null && $transport->margin > 0) 
            echo $transport->margin.' '.$currency;
        else 
            echo trans('front/cart.freeShipping');   
    }

    public function updateTotal(Request $request)
    {
        $currency = Currency::defaultCurrency();
        $transport = TransportMargin::where('min', '<=', convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal()))->where('max', '>=', convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal()))->where('type_id',$request->transportId)->get()->first();
        $total = convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal());
        if($transport != null && $transport->margin > 0) echo floatval($total)+floatval($transport->margin).' '.$currency;
        else echo floatval($total).' '.$currency;   
    }

    public function freeDelivery(Request $request)
    {
        $currency = Currency::defaultCurrency();

        $transportMax = TransportMargin::where('type_id',$request->transportId)->get()->first()->max;
        $total = convertFormattedNumberToFloat(Cart::instance('shopping')->subtotal());
        if($transportMax < 9999 && ($transportMax-$total > 0)) echo $transportMax-$total.' '.$currency;
        else echo trans('front/cart.freeShipping');  
    }

    public function thankYou()
    {
        $client = Auth::guard('client')->user();
        if($client != null) {
            $order = $client->orders()->orderBy('id', 'desc')->limit(1)->get()->first();
        } else {
            $order = Order::whereClient_id(session()->get('orderClientId'))->orderBy('id', 'desc')->limit(1)->get()->first();
        }
        $contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first();
        $company = Company::whereDefault('yes')->get()->first();
        $meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientOrdersCreate';
        return view('front.partials.clients.orders.thankYou', compact('meta', 'breadcrumb', 'order', 'contactInfo', 'order', 'company'));
    }

    public function cancel(Order $order, $id)
    {
        $order = $order->find($id);
        $order->status = 'cancelled';
        if ($order->save()) {
        frontFlash()->success(trans('front/orders.cancelFlashTitle'), trans('front/orders.cancelSuccessText'));
        //$emails = new TransactionalEmails();
        //$emails->updateOrderClientEmails($order);
        } else frontFlash()->error(trans('front/orders.cancelFlashTitle'), trans('front/orders.cancelErrorText'));
        return redirect(route('front-client-orders'));  
    }

    public function setDeliveryInfoRequirements(Request $request)
    {
        $transportType = TransportType::find($request->transportId);
        if ($transportType->show_delivery_address == 'yes') echo true;
        else echo false;
    }


    public function getCities(Request $request)
    {
        $county = County::find($request->countyId);
        $cities = $county->cities()->orderBy('title','ASC')->get();
        return view('front.partials.clients.orders.citiesList', compact('cities'));
    }
    
    public function mobilpayResponse(Request $request)
    {
        MobilPay::processResponse($_POST);
    }
}