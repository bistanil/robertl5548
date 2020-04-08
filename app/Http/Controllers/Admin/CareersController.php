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
use App\Models\Career;
use App\Models\CareerApply;
use App\Http\Requests\Admin\CareerRequest;
use JavaScript;
use URL;

class CareersController extends Controller
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
        $careers = Career::sorted()->paginate(session()->get('careersPerCareer'));
        $breadcrumb='careers';
        return view('admin.partials.careers.main', compact('careers','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='career.create';
        return view('admin.partials.careers.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CareerRequest $request)
    {
        //
        $career = new Career($request->all());
        $career->slug=str_slug($career->title, "-");
        if ($career->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-careers');
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
    public function edit($slug, Career $career)
    {
        //
        $career = $career->bySlug($slug);
        $breadcrumb='career.edit';
        $item=$career;
        return view('admin.partials.careers.form', compact('career','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CareerRequest $request, $slug, Career $career)
    {
        //
        $career=$career->bySlug($slug);
        $career->slug=str_slug($request->title, "-");
        if ($career->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-careers');
    }

    /**
     * Move the Career up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId,$entityId)
    {
        //
        $entity = Career::find($entityId);
        $positionEntity = Career::find($parentId);
        $entity->moveBefore($positionEntity);
        return redirect('admin-careers');
    }

    /**
     * Move the Career down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = Career::find($entityId);
        $positionEntity = Career::find($parentId);
        $entity->moveAfter($positionEntity);
        return redirect('admin-careers');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, Career $career)
    {
        //
        $career=$career->bySlug($slug);        
        if ($career->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-careers');
    }

}
