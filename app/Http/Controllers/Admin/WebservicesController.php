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
use nusoap_client;
use App\Models\Webservice;
use App\Models\ProductPrice;
use App\Http\Requests\Admin\WebserviceRequest;
use App\Events\WebserviceDelete;
use JavaScript;
use URL;

class WebservicesController extends Controller
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
        $webservices = Webservice::get();
        $breadcrumb='webservices';
        return view('admin.partials.webservices.main', compact('webservices','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='webservices.create';
        return view('admin.partials.webservices.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WebserviceRequest $request)
    {
        //
        $webservice = new Webservice($request->all());
        if ($webservice->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-webservices.index'));
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
    public function edit(Webservice $webservice, $id)
    {
        //
        $webservice = $webservice->find($id);        
        $breadcrumb='webservices.edit';
        $item = $webservice;
        return view('admin.partials.webservices.form', compact('webservice','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(WebserviceRequest $request, Webservice $webservice, $id)
    {
        //        
        $webservice=$webservice->find($id);
        if ($webservice->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));       
        return redirect(route('admin-webservices.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Webservice $webservice, $id)
    {
        //
        $webservice=$webservice->find($id);
        if ($webservice->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-webservices.index'));
    }

    /**
     * Update prices from the given webservice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function run(Webservice $webservice, $id)
    {
        $webservice=$webservice->find($id);
        ProductPrice::whereSource('bennett')->delete();
        chdir(base_path());
        exec('bash -c "exec nohup setsid php artisan processbennetwebservice '.$webservice->id.' '.$webservice->key.' > /dev/null 2>&1 &"');
        flash()->success(trans('admin/common.importFlashTitle'), trans('admin/common.importFlashContent'));        
        return redirect('admin');
        return redirect(route('admin-webservices.index'));
    }

    public function runAutonet()
    {
        chdir(base_path());
        exec('bash -c "exec nohup setsid php artisan processautonetcodes  > /dev/null 2>&1 &"');
        flash()->success(trans('admin/common.importFlashTitle'), trans('admin/common.importFlashContent'));        
        return redirect('admin');
        return redirect(route('admin-webservices.index'));
    }
}