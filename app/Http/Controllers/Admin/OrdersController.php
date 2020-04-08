<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use Session;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App;
use Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\TransportMargin;
use App\Models\Client;
use App\Models\Discount;
use App\Models\Currency;
use App\Models\Contact;
use App\Models\Logo;
use App\Models\County;
use App\Models\City;
use App\Models\Supplier;
use App\Models\Awb;
use App\Models\OrderNote;
use App\Models\Payment;
use App\Http\Requests\Admin\OrderRequest;
use App\Http\Libraries\Discounter;
use App\Http\Libraries\AccountCreation;
use PDF;
use App\Events\OrderDelete;
use App\Models\SettingsEmail;
use App\Models\PaymentGateway;
use Notification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\ClientOrderChangedStatus;
use Carbon\Carbon;
use Mail;
use JavaScript;
use URL;
use Excel;
use File;
use Response;
use Storage;

class OrdersController extends Controller
{

    public function __construct(User $user)
    {
        $this->middleware('auth'); 
        JavaScript::put(['baseUrl' => URL::to('/')]);             
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        session()->put('adminItemsUrl',url()->full());
        $orders = Order::orderBy('id','desc')->paginate(session()->get('ordersPerPage'));
        $breadcrumb='orders';
        return view('admin.partials.orders.main', compact('orders','breadcrumb'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, Order $order)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('orderSearch',$request->q);
        $request->session()->keep('orderSearch');         
        $search = $request->session()->get('orderSearch');
        $orders = Order::where('orders.id', 'LIKE', "%$search%")
                          ->orWhere('orders.client_name', 'LIKE', "%$search%")
                          ->orWhere('orders.client_phone', 'LIKE', "%$search%")
                          ->orWhere('orders.client_email', 'LIKE', "%$search%")
                          ->orWhere('orders.client_company_title', 'LIKE', "%$search%")
                          ->orWhere('orders.client_company_fiscal_code', 'LIKE', "%$search%")
                          ->orWhere('orders.client_company_registration_number', 'LIKE', "%$search%")
                          ->orderBy('id','desc')
                          ->paginate(session()->get('ordersPerPage'));
        $breadcrumb='orders';      
        return view('admin.partials.orders.search', compact('orders', 'breadcrumb', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(request $request)
    {
        JavaScript::put(['baseUrl' => URL::to('/')]);
        $catalogProduct = new CatalogProduct();
        $productPrice = new ProductPrice();
        $defaultCurrency = Currency::whereDefault('yes')->get()->first();
        if($request != null) {
            $oldCounty = County::find($request->old('county_id'));
            if($oldCounty != null) $cities = $oldCounty->cities()->orderBy('title')->get();
            else $cities = [];
            $oldTransport = $request->old('transport_type');
            $transport = TransportMargin::where('min', '<=', Cart::subtotal())->where('max', '>=', Cart::subtotal())->where('type_id',$oldTransport)->get()->first();
        } else {
            $cities = [];
            $transport = '';
        }
        $counties = County::whereActive('active')->orderBy('title','ASC')->get();
        $paymentMethods = PaymentGateway::whereActive('active')->sorted()->get();
        $breadcrumb = 'order.create';
        return view('admin.partials.orders.checkout.form', compact('breadcrumb', 'catalogProduct', 'productPrice', 'transport', 'defaultCurrency', 'counties', 'cities','paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request)
    {       
        $client = new AccountCreation($request);
        $result = $client->create('default');
        if ($result == true) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));        
        return redirect('admin-orders');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Order $order)
    {
        $logo = Logo::whereLanguage(App::getLocale())->whereType('proforma')->get()->first();
        $order =  Order::find($id);
        $client = Client::find($order->client_id);
        $orders = Order::whereClient_id($client->id)->get();
        $notes = OrderNote::whereOrder_id($order->id);
        $payments = Payment::whereOrder_id($order->id)->get();
        $suppliers = Supplier::all();
        $breadcrumb = 'order.show';
        $item = $order;
        return view('admin.partials.orders.show', compact('order', 'breadcrumb', 'item', 'logo', 'notes','orders','suppliers','payments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        //
        $order =  Order::find($id);
        $request->session()->put('editOrderId',$order->id);
        Cart::destroy();
        foreach ($order->items as $key => $item) {
            if ($item->price_id != null) Cart::add(['id' => $item->product_id, 'name' => $item->title, 'qty' => $item->qty, 'price' => $item->unit_price, 'options' => ['priceId' => $item->price_id]]);
        }
        return redirect('admin-cart');
    }

    /**
     * Show the form for updating the order.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request, Order $order)
    {
        $order = $order->find($request->session()->get('editOrderId'));
        JavaScript::put(['baseUrl' => URL::to('/')]);
        $catalogProduct = new CatalogProduct();
        $productPrice = new ProductPrice();
        if($request != null) {
            $oldCounty = County::find($request->old('county_id'));
            if($oldCounty != null) $cities = $oldCounty->cities()->orderBy('title')->get();
            else $cities = [];
            $oldTransport = $request->old('transport_type');
            $transport = TransportMargin::where('min', '<=', Cart::subtotal())->where('max', '>=', Cart::subtotal())->where('type_id',$oldTransport)->get()->first();
        } else {
            $cities = [];
            $transport = '';
        }
        $counties = County::whereActive('active')->orderBy('title','ASC')->get();
        $paymentMethods = PaymentGateway::whereActive('active')->sorted()->get();
        $breadcrumb='order.edit';
        $item = $order;
        return view('admin.partials.orders.checkout.updateForm', compact('breadcrumb', 'catalogProduct', 'productPrice', 'transport', 'order', 'item', 'counties', 'cities','paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(OrderRequest $request, Order $order)
    {
        //
        $client = Client::find($request->client_id);
        if ($client != null)
        {
            $order = new PlaceOrder();            
            if ($order->create($request, $client)) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
            else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        } else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if(session()->has('editOrderId')) $request->session()->forget('editOrderId'); 
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));    
        return redirect('admin-orders');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Order $order)
    {
        //
        $order= Order::find($id);        
        event(new OrderDelete($order));
        if ($order->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-orders');
    }

    /**
     * Return order total updated with discounts
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTotal(Request $request, Order $order)
    {
        $order = $order->find($request->session()->get('editOrderId'));
        $client = Client::find($request->clientId);
        $currency = Currency::defaultCurrency();
        if(isset($order) AND isset($order->client->discounts->first()->discount)) {
            $total = convertFormattedNumberToFloat(Cart::subtotal());
            $total = Discounter::apply($total, $order->client->discounts->first()->id);
        } else { 
            $total = convertFormattedNumberToFloat(Cart::subtotal());
        }
        $total = Discounter::apply($total, $request->generalDiscount);        
        if ($client != null) if ($client->discounts->first() != null) $total = Discounter::apply($total, $client->discounts->first()->id);        
        if($request->has('transportId')) {            
            $transport = TransportMargin::where('min', '<=', convertFormattedNumberToFloat(Cart::subtotal()))->where('max', '>=', convertFormattedNumberToFloat( Cart::subtotal()))->where('type_id',$request->transportId)->get()->first();            
            if (isset($transport->margin)) $total += $transport->margin;      
        }                
        echo '<h4>'.trans('admin/orders.total').': '.$total.' '.$currency.'</h4>';        
    }

    public function updateTotalWithTransport(Request $request, Order $order)
    {
        $order = $order->find($request->session()->get('editOrderId'));
        $client = Client::find($request->clientId);
        $currency = Currency::defaultCurrency();
        $transport = TransportMargin::where('min', '<=', convertFormattedNumberToFloat(Cart::subtotal()))->where('max', '>=', convertFormattedNumberToFloat(Cart::subtotal()))->where('type_id',$request->transportId)->get()->first();
        
        if(isset($order) AND isset($order->client->discounts->first()->discount)) {
            $total = convertFormattedNumberToFloat(Cart::subtotal());
            $total = Discounter::apply($total, $order->client->discounts->first()->id);
        } else { 
            $total = convertFormattedNumberToFloat(Cart::subtotal());
        }
        if (isset($request->generalDiscount)) {
            $total = Discounter::apply($total, $request->generalDiscount);            
            if ($client != null) if ($client->discounts->first() != null) $total = Discounter::apply($total, $client->discounts->first()->id);            
        }
        if($transport != null AND $transport->margin > 0) {
        $transportTotal = $total+(string)$transport->margin;
        echo '<h4>'.trans('admin/orders.total').': '.$transportTotal.' '.$currency.'</h4>';
        } else { echo '<h4>'.trans('admin/orders.total').': '.$total.' '.$currency.'</h4>'; }
    }

    public function transportCostAdmin(Request $request)
    {
        $currency = Currency::defaultCurrency();
        $transport = TransportMargin::where('min', '<=', convertFormattedNumberToFloat(Cart::subtotal()))->where('max', '>=', convertFormattedNumberToFloat(Cart::subtotal()))->where('type_id',$request->transportId)->get()->first();
        if($transport != null AND $transport->margin > 0) 
        echo trans('admin/transportMargins.margins').': '.$transport->margin.' '.$currency;
        else 
        echo trans('admin/transportMargins.margins').': '.trans('front/cart.freeShipping');  
    }

    /**
     * Return status change
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus($id, $status)
    {
        $order =  Order::find($id);
        $order->status = $status;
        if($status == 'received') $order->received_at = Carbon::now();
        if ($order->save())  {
        flash()->success(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusSuccessText'));
        Notification::send($order, new ClientOrderChangedStatus($order));
        } else { flash()->error(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusErrorText')); }    
        
        return redirect('admin-orders');
    }

    public function updateOrderedFrom($itemId, Request $request)
    {
       
      $item = OrderItem::find($request->itemId);
      $item->acquisition_supplier_id = $request->acquisition_supplier_id;
      $item->acquisition_supplier_title = $item->supplier->title;
      if ($item->save()) flash()->success(trans('admin/orders.updateSupplierFlashTitle'), trans('admin/orders.updateSupplierSuccessText'));
      else flash()->error(trans('admin/orders.updateSupplierFlashTitle'), trans('admin/orders.updateSupplierErrorText'));    
      return back();
    }

    /**
     * Return order proforma in PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function proformaPdf($id)
    {
        $contactInfo = Contact::whereLanguage(App::getLocale())->get()->first();
        $order = Order::find($id);
        $logo = Logo::whereType('proforma')->whereLanguage(locale())->get()->first();
        if ($order != null)
        {
            $headerHtml = view()->make('admin.partials.orders.proforma.header', compact('logo','order', 'contactInfo'))->render();
            $footerHtml = view()->make('admin.partials.orders.proforma.footer', compact('logo','order', 'contactInfo'))->render();
            $pdf = PDF::loadView('admin.layouts.proforma', compact('logo','order', 'contactInfo'))
               ->setPaper('a4')
               ->setOption('margin-top', '30mm')
               ->setOption('margin-bottom', '30mm')
               ->setOption('header-html', $headerHtml)
               ->setOption('footer-html', $footerHtml);
            return $pdf->inline(trans('admin/orders.proforma').'-'.$order->id.'.pdf');
        }
    }

    public function getCities(Request $request)
    {
        $county = County::find($request->countyId);
        $cities = $county->cities()->orderBy('title','ASC')->get();
        return view('admin.partials.orders.checkout.citiesList', compact('cities'));
    }

    public function updateInternStatus($id, $internStatus)
    {
        $order = Order::find($id);
        $order->intern_status = $internStatus;
        if ($order->save())flash()->success(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusSuccessText'));
        else flash()->error(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusErrorText'));   
        return redirect('admin-orders');
    }
}