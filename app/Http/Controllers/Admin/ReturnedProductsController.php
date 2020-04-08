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
use App\Models\ReturnedProduct;

class ReturnedProductsController extends Controller
{
    public function __construct(User $user)
    {
        $this->middleware('auth');              
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        session()->put('adminItemsUrl',url()->full());
        $returnedProducts = ReturnedProduct::orderBy('id', 'desc')->paginate(session()->get('requestsPerPage'));
        $breadcrumb='returnedProducts';
        return view('admin.partials.returnedProducts.main', compact('returnedProducts','breadcrumb'));
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
        $request = ReturnedProduct::find($id);
        $request->status = 'read';
        $request->save();
        $breadcrumb = 'returnedProducts.show';
        $item = $request;
        return view('admin.partials.returnedProducts.request', compact('request', 'item', 'breadcrumb'));
    }

    public function destroy($id, ReturnedProduct $item)
    {
        //
        $item=$item->find($id);
        if ($item->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-returned-products.index'));
    }

    public function updateStatus($id, $status)
    {
        $message = ReturnedProduct::find($id);
        $message->second_status = $status;
        if ($message->save()) flash()->success(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusSuccessText'));
        else flash()->error(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusErrorText'));  
        return redirect(route('admin-contact-messages.index'));
    }

}
