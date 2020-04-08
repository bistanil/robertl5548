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
use App\Models\Discount;
use App\Http\Requests\Admin\DiscountRequest;
use JavaScript;
use URL;

class DiscountsController extends Controller
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
        $discounts = Discount::paginate(session()->get('discountsPerPage'));
        $breadcrumb='discounts';
        return view('admin.partials.discounts.main', compact('discounts','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb = 'discounts.create';
        return view('admin.partials.discounts.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DiscountRequest $request)
    {
        //
        $discount = new Discount($request->all());
        if($request->client_id == '') $discount->client_id = 0;
        $discount->discount = 1 - intval($discount->discount)/100;
        if ($discount->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-discounts');
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
    public function edit($id, Discount $discount)
    {
        //
        $discount = $discount->find($id);
        $breadcrumb='discounts.edit';
        $item=$discount;
        return view('admin.partials.discounts.form', compact('discount','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DiscountRequest $request, $id, Discount $discount)
    {
        //
        $discount=$discount->find($id);
        $discount->discount = 1 - intval($request->discount)/100;
        if($request->client_id == '') $discount->client_id = 0;
        else $discount->client_id = $request->client_id;
        if ($discount->update()) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect('admin-discounts');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Discount $discount)
    {
        //
        $discount=$discount->find($id);        
        if ($discount->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-discounts');
    }
}
