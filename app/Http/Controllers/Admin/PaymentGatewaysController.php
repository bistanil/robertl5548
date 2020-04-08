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
use App\Models\PaymentGateway;
use App\Http\Requests\Admin\PaymentGatewayRequest;
use URL;
use JavaScript;

class PaymentGatewaysController extends Controller
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
        $gateways = PaymentGateway::sorted()->get();
        $breadcrumb='paymentGateways';
        return view('admin.partials.settings.paymentGateways.main', compact('gateways','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='paymentGateway.create';
        return view('admin.partials.settings.paymentGateways.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PaymentGatewayRequest $request)
    {
        $gateway = new PaymentGateway($request->all());
        $gateway->image = hwImage()->widen($request, 'paymentGateway');
        $gateway->public_key = hwImage()->key($request, 'gateways', 'public_key');
        $gateway->private_key = hwImage()->key($request, 'gateways', 'private_key');
        if ($gateway->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-settings-gateways');
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
    public function edit($id, PaymentGateway $gateway)
    {
        $gateway=$gateway->find($id);
        $breadcrumb='paymentGateway.edit';
        $item=$gateway;
        return view('admin.partials.settings.paymentGateways.form', compact('gateway','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PaymentGatewayRequest $request, $id, PaymentGateway $gateway)
    {
        //
        $gateway=$gateway->find($id);
        if ($gateway->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($gateway->image, 'paymentGateway');
            $gateway->image = hwImage()->widen($request, 'paymentGateway');
        }
        if ($request->hasFile('public_key'))
        {
            hwImage()->destroy($gateway->public_key, 'gateways');
            $gateway->public_key = hwImage()->key($request, 'gateways', 'public_key');            
        }
        if ($request->hasFile('private_key'))
        {
            hwImage()->destroy($gateway->private_key, 'gateways');
            $gateway->private_key = hwImage()->key($request, 'gateways', 'private_key');
        }        
        $gateway->save();
        return redirect('admin-settings-gateways');
    }

    /**
     * Move the banner up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId, $entityId)
    {
        //
        $entity = PaymentGateway::find($entityId);
        $positionEntity = PaymentGateway::find($parentId);
        $entity->moveBefore($positionEntity);
        return redirect('admin-settings-gateways');
    }

    /**
     * Move the banner down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId, $entityId)
    {
        $entity = PaymentGateway::find($entityId);
        $positionEntity = PaymentGateway::find($parentId);
        $entity->moveAfter($positionEntity);
        return redirect('admin-settings-gateways');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, PaymentGateway $gateway)
    {
        //
        $gateway=$gateway->find($id);
        hwImage()->destroy($gateway->image, 'paymentGateway');
        hwImage()->destroy($gateway->public_key, 'gateways');
        hwImage()->destroy($gateway->private_key, 'gateways');
        if ($gateway->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-settings-gateways');
    }
}
