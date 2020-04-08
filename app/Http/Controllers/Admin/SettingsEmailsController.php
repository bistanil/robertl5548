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
use App\Models\SettingsEmail;
use App\Http\Requests\Admin\SettingsEmailRequest;
use JavaScript;
use URL;

class SettingsEmailsController extends Controller
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
        $emails = SettingsEmail::all();
        $breadcrumb='settingsEmails';
        return view('admin.partials.settings.emails.main', compact('emails','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='settingsEmails.create';
        return view('admin.partials.settings.emails.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SettingsEmailRequest $request)
    {
        $email = new SettingsEmail($request->all());
        if ($email->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-settings-email');
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
    public function edit($id, SettingsEmail $email)
    {
        $email=$email->find($id);
        $breadcrumb='settingsEmails.edit';
        $item=$email;
        return view('admin.partials.settings.emails.form', compact('email','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SettingsEmailRequest $request, $id, SettingsEmail $email)
    {
        //
        $email=$email->find($id);
        if ($email->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-settings-email');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, SettingsEmail $email)
    {
        //
        $email=$email->find($id);
        if ($email->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-settings-email');
    }
}
