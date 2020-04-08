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
use App\Models\Catalog;
use App\Models\CatalogList;
use App\Http\Requests\Admin\CatalogListRequest;
use App\Events\CatalogListDelete;
use JavaScript;
use URL;


class CatalogListsController extends Controller
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
    public function index($slug, Catalog $catalog)
    {
        session()->put('adminItemsUrl',url()->full());
        $parent=$catalog->bySlug($slug);
        $lists = $parent->lists($slug)->paginate(session()->get('listsPerPage'));
        $breadcrumb='catalogLists';
        $item=$parent;        
        return view('admin.partials.catalogs.lists.main', compact('lists','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, Catalog $catalog)
    {
        $parent=$catalog->bySlug($slug);
        $breadcrumb='catalogLists.create';
        $item=$parent;
        return view('admin.partials.catalogs.lists.form', compact('breadcrumb','parent','item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CatalogListRequest $request, Catalog $catalog, $slug)
    {
        //
        $list = new CatalogList($request->all());
        $catalog = $catalog->bySlug($slug);
        $list->slug=str_slug($catalog->title.'-'.$list->title, "-");
        $list->catalog_id = $catalog->id;
        $list->language = $catalog->language;
        if ($list->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-lists.index', ['catalogSlug' => $catalog->slug] ));
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
    public function edit(Catalog $catalog, $catalogSlug, CatalogList $list, $listSlug)
    {
        //
        $list = $list->bySlug($listSlug);        
        $parent = $catalog->bySlug($catalogSlug);
        $breadcrumb='catalogLists.edit';
        $item=$list;
        return view('admin.partials.catalogs.lists.form', compact('parent','list','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CatalogListRequest $request, Catalog $catalog, $catalogSlug, CatalogList $list, $listSlug)
    {
        //        
        $list=$list->bySlug($listSlug);
        $catalog = $catalog->bySlug($catalogSlug);
        $list->slug=str_slug($catalog->title.'-'.$list->title, "-");
        $parent=$list->catalog;
        if ($list->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-catalog-lists.index', ['catalogSlug' => $catalogSlug] ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $catalogSlug, CatalogList $list, $listSlug)
    {
        //
        $list=$list->bySlug($listSlug);
        event(new CatalogListDelete($list));
        $parent=$list->where('id', $list->parent)->first();
        if ($list->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-catalog-lists.index', ['catalogSlug' => $catalogSlug] ));
    }
}
