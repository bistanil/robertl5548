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
use App\Models\AwbWeight;
use App\Http\Requests\Admin\AwbWeightRequest;

class WeightsController extends Controller
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
        $weights = AwbWeight::all();
        $breadcrumb='weights';
        return view('admin.partials.awbs.weights.main', compact('weights','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='weights.create';
        return view('admin.partials.awbs.weights.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AwbWeightRequest $request)
    {
        $weight = new AwbWeight($request->all());
        if ($weight->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-awb-weights');
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
    public function edit($id, AwbWeight $weight)
    {
        $weight=$weight->find($id);
        $breadcrumb='weights.edit';
        $item=$weight;
        return view('admin.partials.awbs.weights.form', compact('weight','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AwbWeightRequest $request, $id, AwbWeight $weight)
    {
        //
        $weight=$weight->find($id);
        if ($weight->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-awb-weights');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, AwbWeight $weight)
    {
        //
        $weight=$weight->find($id);
        if ($weight->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-awb-weights');
    }
}
