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
use App\Models\PriceMargin;
use App\Models\Manufacturer;
use App\Models\PartsCategory;
use App\Http\Requests\Admin\PriceMarginRequest;
use JavaScript;
use URL;

class PriceMarginsController extends Controller
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
        $priceMargins = PriceMargin::paginate();
        $breadcrumb='priceMargins';
        return view('admin.partials.priceMargins.main', compact('priceMargins','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb = 'priceMargins.create';
        $manufacturers = Manufacturer::sorted()->get();
        $categories = PartsCategory::whereGroup(1)->sorted()->get();
        return view('admin.partials.priceMargins.form', compact('breadcrumb', 'manufacturers', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PriceMarginRequest $request)
    {
        //
        $priceMargin = new PriceMargin($request->all());
        if ($priceMargin->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-price-margins');
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
    public function edit($id, PriceMargin $priceMargin)
    {
        //
        $priceMargin = $priceMargin->find($id);
        $manufacturers = Manufacturer::sorted()->get();
        $categories = PartsCategory::whereGroup(1)->sorted()->get();
        $breadcrumb='priceMargins.edit';
        $item=$priceMargin;
        return view('admin.partials.priceMargins.form', compact('priceMargin','breadcrumb','item', 'manufacturers', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PriceMarginRequest $request, $id, PriceMargin $priceMargin)
    {
        //
        $priceMargin=$priceMargin->find($id);
        if ($priceMargin->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect('admin-price-margins');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, PriceMargin $priceMargin)
    {
        //
        $priceMargin=$priceMargin->find($id);        
        if ($priceMargin->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-price-margins');
    }
}
