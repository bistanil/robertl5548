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
use App\Models\Banner;
use App\Http\Requests\Admin\BannerRequest;
use JavaScript;
use URL;

class BannersController extends Controller
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
        $banners = Banner::sorted()->paginate(session()->get('bannersPerPage'));
        $breadcrumb='banners';
        return view('admin.partials.banners.main', compact('banners','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='banner.create';
        return view('admin.partials.banners.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BannerRequest $request)
    {
        $banner = new Banner($request->all());
        $banner->image = hwImage()->widen($request, 'banner');
        if ($banner->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-banners');
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
    public function edit($id, Banner $banner)
    {
        $banner=$banner->find($id);
        $breadcrumb='banner.edit';
        $item=$banner;
        return view('admin.partials.banners.form', compact('banner','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BannerRequest $request, $id, Banner $banner)
    {
        //
        $banner=$banner->find($id);
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($banner->image, 'banner');
            $banner->image = hwImage()->widen($request, 'banner');
        }
        if ($banner->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-banners');
    }

    /**
     * Move the banner up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId,$entityId)
    {
        //
        $entity = Banner::find($entityId);
        $positionEntity = Banner::find($parentId);
        $entity->moveBefore($positionEntity);
        return redirect('admin-banners');
    }

    /**
     * Move the banner down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = Banner::find($entityId);
        $positionEntity = Banner::find($parentId);
        $entity->moveAfter($positionEntity);
        return redirect('admin-banners');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Banner $banner)
    {
        //
        $banner=$banner->find($id);
        hwImage()->destroy($banner->image, 'banner');
        if ($banner->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-banners');
    }
}
