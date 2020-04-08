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
use App\Models\Staticmeta;
use App\Http\Requests\Admin\StaticmetaRequest;
use JavaScript;
use URL;

class SettingsStaticmetaController extends Controller
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
        $staticmetas = Staticmeta::all();
        $breadcrumb='settingsStaticmeta';
        return view('admin.partials.settings.staticmeta.main', compact('staticmetas','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='settingsStaticmeta.create';
        return view('admin.partials.settings.staticmeta.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StaticmetaRequest $request)
    {
        $staticmeta = new Staticmeta($request->all());
        if ($staticmeta->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-settings-static-meta');
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
    public function edit($id, Staticmeta $staticmeta)
    {
        $staticmeta=$staticmeta->find($id);
        $breadcrumb='settingsStaticmeta.edit';
        $item=$staticmeta;
        return view('admin.partials.settings.staticmeta.form', compact('staticmeta','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StaticmetaRequest $request, $id, Staticmeta $staticmeta)
    {
        //
        $staticmeta=$staticmeta->find($id);
        if ($staticmeta->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-settings-static-meta');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Staticmeta $staticmeta)
    {
        //
        $staticmeta=$staticmeta->find($id);
        if ($staticmeta->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-settings-static-meta');
    }
}
