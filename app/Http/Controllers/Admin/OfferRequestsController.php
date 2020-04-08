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
use App\Models\OfferRequest;
use App\Models\CarModelType;
use JavaScript;
use URL;

class OfferRequestsController extends Controller
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
        $requests = OfferRequest::orderBy('id', 'desc')->paginate(session()->get('requestsPerPage'));
        $breadcrumb='offerRequests';
        return view('admin.partials.offerRequests.main', compact('requests','breadcrumb'));
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
        $request = OfferRequest::find($id);
        $request->status = 'read';
        $request->save();
        $breadcrumb = 'offerRequests.show';
        $item = $request;
        $type = CarModelType::find($request->type_id);
        return view('admin.partials.offerRequests.request', compact('request', 'item', 'breadcrumb', 'type'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, OfferRequest $request)
    {
        //
        $request=$request->find($id);
        if ($request->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-offer-requests.index'));
    }

    public function updateStatus($id, $status)
    {
        $request = OfferRequest::find($id);
        $request->second_status = $status;
        if ($request->save()) flash()->success(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusSuccessText'));
        else flash()->error(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusErrorText'));
        return redirect(route('admin-offer-requests.index'));
    }
}
