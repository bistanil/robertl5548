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
use App\Models\Socialmedia;
use App\Http\Requests\Admin\SocialmediaRequest;
use JavaScript;
use URL;

class SettingsSocialmediaController extends Controller
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
        $socialmedias = Socialmedia::all();
        $breadcrumb='settingsSocialmedia';
        return view('admin.partials.settings.socialmedia.main', compact('socialmedias','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='settingsSocialmedia.create';
        return view('admin.partials.settings.socialmedia.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SocialmediaRequest $request)
    {
        $socialmedia = new Socialmedia($request->all());
        if ($socialmedia->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-settings-social-media');
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
    public function edit($id, Socialmedia $socialmedia)
    {
        $socialmedia=$socialmedia->find($id);
        $breadcrumb='settingsSocialmedia.edit';
        $item=$socialmedia;
        return view('admin.partials.settings.socialmedia.form', compact('socialmedia','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SocialmediaRequest $request, $id, Socialmedia $socialmedia)
    {
        //
        $socialmedia=$socialmedia->find($id);
        if ($socialmedia->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-settings-social-media');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Socialmedia $socialmedia)
    {
        //
        $socialmedia=$socialmedia->find($id);
        if ($socialmedia->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-settings-social-media');
    }
}
