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
use App\Models\Watermark;
use App\Http\Requests\Admin\WatermarkRequest;
use JavaScript;
use URL;


class SettingsWatermarkController extends Controller
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
        $watermarks = Watermark::all();
        $breadcrumb='settingsWatermark';
        return view('admin.partials.settings.watermark.main', compact('watermarks','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='settingsWatermark.create';
        return view('admin.partials.settings.watermark.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WatermarkRequest $request)
    {
        $watermark = new Watermark($request->all());
        $watermark->image = hwImage()->heighten($request, $watermark->type);
        if ($watermark->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-settings-watermark');
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
    public function edit($id, Watermark $watermark)
    {
        $watermark=$watermark->find($id);
        $breadcrumb='settingsWatermark.edit';
        $item=$watermark;
        return view('admin.partials.settings.watermark.form', compact('watermark','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(WatermarkRequest $request, $id, Watermark $watermark)
    {
        //
        $watermark=$watermark->find($id);
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($watermark->image, $watermark->type);
            $watermark->image = hwImage()->heighten($request, $watermark->type);
        }
        if ($watermark->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-settings-watermark');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Watermark $watermark)
    {
        //
        $watermark=$watermark->find($id);
        hwImage()->destroy($watermark->image, $watermark->type);
        if ($watermark->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-settings-watermark');
    }
}
