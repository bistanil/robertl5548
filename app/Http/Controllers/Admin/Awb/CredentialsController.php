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
use App\Models\AwbCredential;
use App\Http\Requests\Admin\AwbCredentialRequest;

class CredentialsController extends Controller
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
        $credentials = AwbCredential::all();
        $breadcrumb='credentials';
        return view('admin.partials.awbs.credentials.main', compact('credentials','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='credentials.create';
        return view('admin.partials.awbs.credentials.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AwbCredentialRequest $request)
    {
        $credential = new AwbCredential($request->all());
        if ($credential->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-awb-credentials');
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
    public function edit($id, AwbCredential $credential)
    {
        $credential=$credential->find($id);
        $breadcrumb='credentials.edit';
        $item=$credential;
        return view('admin.partials.awbs.credentials.form', compact('credential','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AwbCredentialRequest $request, $id, AwbCredential $credential)
    {
        //
        $credential=$credential->find($id);
        if ($credential->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-awb-credentials');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, AwbCredential $credential)
    {
        //
        $credential=$credential->find($id);
        if ($credential->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-awb-credentials');
    }
}
