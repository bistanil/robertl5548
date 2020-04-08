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
use App\Models\Slide;
use App\Http\Requests\Admin\SlideRequest;
use JavaScript;
use URL;

class SlidesController extends Controller
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
        $slides = Slide::sorted()->paginate(session()->get('slidesPerPage'));
        $breadcrumb='slides';
        return view('admin.partials.slides.main', compact('slides','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='slide.create';
        return view('admin.partials.slides.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SlideRequest $request)
    {
        $slide = new Slide($request->all());
        $slide->image = hwImage()->heighten($request, 'slide');
        if ($slide->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-slides');
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
    public function edit($id, Slide $slide)
    {
        $slide=$slide->find($id);
        $breadcrumb='slide.edit';
        $item=$slide;
        return view('admin.partials.slides.form', compact('slide','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SlideRequest $request, $id, Slide $slide)
    {
        //
        $slide=$slide->find($id);
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($slide->image, 'slide');
            $slide->image = hwImage()->heighten($request, 'slide');
        }
        if ($slide->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-slides');
    }

    /**
     * Move the slide up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId,$entityId)
    {
        //
        $entity = Slide::find($entityId);
        $positionEntity = Slide::find($parentId);
        $entity->moveBefore($positionEntity);
        return redirect('admin-slides');
    }

    /**
     * Move the slide down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = Slide::find($entityId);
        $positionEntity = Slide::find($parentId);
        $entity->moveAfter($positionEntity);
        return redirect('admin-slides');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Slide $slide)
    {
        //
        $slide=$slide->find($id);
        hwImage()->destroy($slide->image, 'slide');
        if ($slide->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-slides');
    }
}
