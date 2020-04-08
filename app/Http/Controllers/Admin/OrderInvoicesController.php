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
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\SettingsEmail;
use App\Http\Requests\Admin\OrderInvoiceRequest;
use App\Notifications\SendOrderInvoice;
use App\Notifications\AdminSendOrderInvoice;
use JavaScript;
use URL;
use Mail;
use Notification;

class OrderInvoicesController extends Controller
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
    public function index($id, Order $order)
    {
        session()->put('adminItemsUrl',url()->full());
        $order = $order->find($id);
        $invoices = $order->invoices()->orderBy('created_at','desc')->paginate();
        $breadcrumb='orderInvoices';
        $item = $order;
        return view('admin.partials.orders.invoices.main', compact('invoices','breadcrumb', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, Order $order)
    {
        $item = $order->find($id);
        $breadcrumb='orderInvoice.create';
        return view('admin.partials.orders.invoices.form', compact('breadcrumb', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id, Order $order, OrderInvoiceRequest $request)
    {
        //
        $order = $order->find($id);
        $invoice = new OrderInvoice($request->all());
        $invoice->order_id = $order->id;
        $invoice->client_id = $order->client_id;
        $invoice->client_email = $order->client_email;
        $invoice->client_name = $order->client_name;
        $invoice->docs = hwImage()->file($request, 'orderInvoice');
        if ($invoice->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-order-invoices.index', ['orderId' => $order->id]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, OrderInvoice $invoice)
    {
        //
        $invoice = $invoice->find($id);
        $breadcrumb='orderInvoice.edit';
        $item=$invoice;
        return view('admin.partials.orders.invoices.form', compact('invoice','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(OrderInvoiceRequest $request, $id, OrderInvoice $invoice)
    {
        //
        $invoice=$invoice->find($id);
        if ($request->hasFile('docs'))
        {
            hwImage()->destroy($invoice->docs, 'orderInvoice');
            $invoice->docs = hwImage()->file($request, 'orderInvoice');
        }          
        if ($invoice->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-order-invoices.index', ['orderId' => $invoice->order->id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, OrderInvoice $invoice)
    {
        //
        $invoice=$invoice->find($id);
        hwImage()->destroy($invoice->docs, 'orderInvoice');         
        if ($invoice->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-order-invoices.index', ['orderId' => $invoice->order->id]));
    }

    public function sendInvoice(Request $request)
    {
        $invoice = OrderInvoice::find($request->invoice_id);
        Notification::send($invoice, new SendOrderInvoice($invoice));
        $adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
        Notification::send($adminEmail, new AdminSendOrderInvoice($invoice));  
        flash()->success(trans('admin/clients.invoiceSentFlashTitle'), trans('admin/clients.invoiceSentSuccessText')); 
        return redirect(route('admin-order-invoices.index', ['orderId' => $invoice->order->id]));
    }
}