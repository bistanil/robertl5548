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
use App\Models\TransportType;
use App\Models\TransportMargin;
use App\Http\Requests\Admin\TransportMarginRequest;
use JavaScript;
use URL;

class TransportMarginsController extends Controller
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
        $transportMargins = TransportMargin::paginate();
        $breadcrumb='transportMargins';
        return view('admin.partials.transport.margins.main', compact('transportMargins','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb = 'transportMargins.create';
        $types=TransportType::whereActive('active')->get();
        return view('admin.partials.transport.margins.form', compact('breadcrumb','types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TransportMarginRequest $request)
    {
        //
        $transportMargin = new transportMargin($request->all());
        if ($transportMargin->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-transport-margins');
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
    public function edit($id, transportMargin $transportMargin)
    {
        //
        $transportMargin = $transportMargin->find($id);
        $types=TransportType::whereActive('active')->get();
        $breadcrumb='transportMargins.edit';
        $item=$transportMargin;
        return view('admin.partials.transport.margins.form', compact('transportMargin','breadcrumb','item','types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TransportMarginRequest $request, $id, transportMargin $transportMargin)
    {
        //
        $transportMargin=$transportMargin->find($id);
        if ($transportMargin->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect('admin-transport-margins');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, TransportMargin $transportMargin)
    {
        //
        $transportMargin=$transportMargin->find($id);        
        if ($transportMargin->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-transport-margins');
    }
}