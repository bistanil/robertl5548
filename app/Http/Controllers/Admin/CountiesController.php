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
use App\Models\County;
use App\Events\CountyDelete;
use App\Http\Requests\Admin\CountyRequest;

class CountiesController extends Controller
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
        $counties = County::orderBy('title', 'asc')->paginate(50);
        $breadcrumb='counties';
        return view('admin.partials.counties.main', compact('counties','breadcrumb'));
    }

     public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('countySearch',$request->q);
        $request->session()->keep('countySearch');         
        $search = $request->session()->get('countySearch');
        $counties = County::where('counties.title', 'LIKE', "%$search%")
                            ->paginate(session()->get('countiesPerPage'));
        $breadcrumb='counties';
        return view('admin.partials.counties.search', compact('counties', 'breadcrumb', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='counties.create';
        return view('admin.partials.counties.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CountyRequest $request)
    {
        //
        $county = new County($request->all());
        if ($county->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-counties');
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
    public function edit($id, County $county)
    {
        //
        $county = $county->find($id);
        $breadcrumb='counties.edit';
        $item=$county;
        return view('admin.partials.counties.form', compact('county','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CountyRequest $request, $id, County $county)
    {
        //
        $county=$county->find($id);
        if ($county->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));    
        return redirect('admin-counties');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, County $county)
    {
        //
        $county=$county->find($id);  
        event(new CountyDelete($county));       
        if ($county->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));  
        return redirect('admin-counties');
    }
}
