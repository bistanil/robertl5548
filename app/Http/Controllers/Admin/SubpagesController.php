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
use App\Models\Page;
use App\Http\Requests\Admin\PageRequest;
use App\Http\Requests\Admin\SubpageRequest;
use App\Events\PageDelete;
use JavaScript;
use URL;

class SubpagesController extends Controller
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
    public function index($slug, Page $page)
    {
        $parent=$page->bySlug($slug);
        $pages = $page->subpages($slug);
        $breadcrumb='subpages';
        $item=$parent;        
        return view('admin.partials.pages.subpages.main', compact('pages','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, Page $page)
    {
        $parent=$page->bySlug($slug);
        $breadcrumb='subpage.create';
        $item=$parent;
        return view('admin.partials.pages.subpages.form', compact('breadcrumb','parent','item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PageRequest $request, $slug)
    {
        //
        $page = new Page($request->all());
        $page->slug=str_slug($page->title, "-");
        if ($page->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();
        return redirect('admin-subpages/'.$slug);
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
    public function edit($slug, Page $page)
    {
        //
        $page = $page->bySlug($slug);
        $breadcrumb='subpage.edit';
        $item=$page;
        return view('admin.partials.pages.subpages.form', compact('page','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SubpageRequest $request, $slug, Page $page)
    {
        //        
        $page=$page->bySlug($slug);
        $parent=$page->where('id', $page->parent)->first();
        $page->slug=str_slug($request->title, "-");
        if ($page->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();        
        return redirect('admin-subpages/'.$parent->slug);
    }

    /**
     * Move the page up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId,$entityId)
    {
        //
        $entity = Page::find($entityId);
        $positionEntity = Page::find($parentId);
        $entity->moveBefore($positionEntity);
        $parent=Page::where('id', $entity->parent)->first();
        return redirect('admin-subpages/'.$parent->slug);
    }

    /**
     * Move the page down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = Page::find($entityId);
        $positionEntity = Page::find($parentId);
        $entity->moveAfter($positionEntity);
        $parent=Page::where('id', $entity->parent)->first();
        return redirect('admin-subpages/'.$parent->slug);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, Page $page)
    {
        //
        $page=$page->bySlug($slug);
        event(new PageDelete($page));
        $parent=$page->where('id', $page->parent)->first();
        if ($page->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-subpages/'.$parent->slug);
    }
}
