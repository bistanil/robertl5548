<?php

namespace App\Http\Controllers\Admin\Awb;

use Illuminate\Http\Request;
use Auth;
use Session;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App;
use App\Models\Awb;
use App\Models\Order;
use App\Models\AwbCredential;
use App\Http\Requests\Admin\AwbRequest;
use App\Http\Libraries\Awb\FanCourier;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\ClientOrderChangedStatus;
use Notification;
use Storage;
use PDF;
use Excel;
use File;
use Response;


class FanCourierController extends Controller
{
    
    public function __construct(User $user)
    {
        $this->middleware('auth');              
    }

    public function index()
    {

    }

    public function create($id, Order $order)
    {
        $order = $order->find($id);
        if($order->awb != null) $awb = $order->awb;
        else $awb = '';
        $breadcrumb = 'generateAwb';
        $item = $order;
        return view('admin.partials.awbs.form', compact('order', 'breadcrumb', 'item', 'awb'));
    }

    public function generate(AwbRequest $request, Order $order)
    {
        $order = $order->find($request->order_id);
        $awb = new FanCourier($request,$order);
        $awb = $awb->create(); 
        $order->status = 'in_progress';
        $order->save();
        //client email order edited by admin
        Notification::send($order, new ClientOrderChangedStatus($order));
        Storage::disk('proforma')->delete('proforma'.$order->id.'.pdf'); 
        flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        return redirect(route('admin-orders.show', ['id' => $order->id]));
    }

    public function edit($id, Order $order)
    {
        $order = $order->find($id);
        if($order->awb != null) $awb = $order->awb;
        else $awb = '';
        $breadcrumb = 'generateAwb';
        $item = $order;
        return view('admin.partials.awbs.editForm', compact('order', 'breadcrumb', 'item', 'awb'));
    }

   
    public function download($id, Order $order)
    {
      $order = $order->find($id);
      $credential = AwbCredential::whereType('fanCourier')->get()->first();
      $ch = curl_init('http://www.selfawb.ro/view_awb_integrat.php'); 
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));
      curl_setopt($ch, CURLOPT_POSTFIELDS, array('nr'=> $order->awbDetail->awb_number,'username' => $credential->username,'client_id' => $credential->client_id,'user_pass' => $credential->user_pass,'language' => 'ro'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch,CURLOPT_FAILONERROR,true);
      $postResult = curl_exec($ch);
      curl_close($ch);
      $pdf = PDF::loadHTML($postResult);
      Storage::disk('awb')->put('awb-'.$order->id.'.pdf', $pdf->output());
      return $pdf->download('awb-'.$order->id.'.pdf');

    }
}
