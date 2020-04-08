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
use App\Events\PageDelete;
use JavaScript;
use URL;

class PagesController extends Controller
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
        $pages = Page::sorted()->where('parent', 0)->paginate(session()->get('pagesPerPage'));
        $breadcrumb='pages';
        return view('admin.partials.pages.main', compact('pages','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='page.create';
        return view('admin.partials.pages.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PageRequest $request)
    {
        //
        $page = new Page($request->all());
        $page->slug=str_slug($page->title, "-");
        $page->parent=0;
        if ($page->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();
        return redirect('admin-pages');
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
        $breadcrumb='page.edit';
        $item=$page;
        return view('admin.partials.pages.form', compact('page','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PageRequest $request, $slug, Page $page)
    {
        //
        $page=$page->bySlug($slug);
        $page->slug=str_slug($request->title, "-");
        if ($page->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        if ($request->saveAndStay == 'true') return redirect()->back();
        return redirect('admin-pages');
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
        return redirect('admin-pages');
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
        return redirect('admin-pages');
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
        if ($page->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-pages');
    }
}
