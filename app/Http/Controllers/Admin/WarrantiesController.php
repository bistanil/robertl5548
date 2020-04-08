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
use App\Models\OrderItem;
use App\Models\Warranty;
use App\Models\SettingsEmail;
use App\Http\Requests\Admin\WarrantyRequest;
use App\Notifications\SendWarranty;
use App\Notifications\AdminSendWarranty;
use Carbon\Carbon;
use JavaScript;
use URL;
use Mail;
use Notification;

class WarrantiesController extends Controller
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
        $warranties = new Warranty;
        if (session()->get('warrantyStartDate') != null) $warranties = $warranties->where('start_date', '>=', session()->get('warrantyStartDate'));
        if (session()->get('warrantyExpirationDate') != null) $warranties = $warranties->where('expiration_date', '<=', session()->get('warrantyExpirationDate'));
        $warranties = $order->warranties()->orderBy('start_date','desc')->paginate();
        $breadcrumb='orderWarranties';
        $item = $order;
        return view('admin.partials.orders.warranties.main', compact('warranties','breadcrumb', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, Order $order)
    {
        $order = $order->find($id);
        $orderItems = $order->items;
        $item = $order;
        $breadcrumb = 'orderWarranty.create';
        return view('admin.partials.orders.warranties.form', compact('breadcrumb', 'item', 'orderItems'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id, Order $order, WarrantyRequest $request)
    {
        //
        $order = $order->find($id);
        $warranty = new Warranty($request->all());
        $warranty->order_id = $order->id;
        $warranty->product_title = $order->items->where('product_id', $warranty->product_id)->first()->title;
        $warranty->client_id = $order->client_id;
        $warranty->client_email = $order->client_email;
        $warranty->client_name = $order->client_name;
        $warranty->docs = hwImage()->file($request, 'orderWarranty');
        if ($warranty->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-order-warranties.index', ['orderId' => $order->id]));
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
    public function edit($id, Warranty $warranty)
    {
        //
        $warranty = $warranty->find($id);
        $order = $warranty->order;
        $orderItems = $order->items;
        $breadcrumb='orderWarranty.edit';
        $item = $warranty;
        return view('admin.partials.orders.warranties.form', compact('warranty','breadcrumb','item', 'orderItems', 'order'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(WarrantyRequest $request, $id, Warranty $warranty)
    {
        //
        $warranty=$warranty->find($id);
        $warranty->product_title = $warranty->order->items->where('product_id', $request->product_id)->first()->title;
        if ($request->hasFile('docs'))
        {
            hwImage()->destroy($warranty->docs, 'orderWarranty');
            $warranty->docs = hwImage()->file($request, 'orderWarranty');
        }  
        if ($warranty->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-order-warranties.index', ['orderId' => $warranty->order->id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Warranty $warranty)
    {
        //
        $warranty=$warranty->find($id);
        hwImage()->destroy($warranty->docs, 'orderWarranty');         
        if ($warranty->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-order-warranties.index', ['orderId' => $warranty->order->id]));
    }

    public function sendWarranty(Request $request)
    {
        $warranty = Warranty::find($request->warranty_id);
        Notification::send($warranty, new SendWarranty($warranty));
        $adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
        Notification::send($adminEmail, new AdminSendWarranty($warranty));  
        flash()->success(trans('admin/clients.warrantySentFlashTitle'), trans('admin/clients.warrantySentSuccessText')); 
        return redirect(route('admin-order-warranties.index', ['orderId' => $warranty->order->id]));
    }
}