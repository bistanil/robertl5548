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
use App\Models\CatalogCategory;
use App\Http\Requests\Admin\CatalogCategoryRequest;
use App\Events\CatalogCategoryDelete;

class CatalogSubcategoriesController extends Controller
{

    public function __construct(User $user)
    {
        $this->middleware('auth');              
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Catalog $catalog, CatalogCategory $category, $slug)
    {
        session()->put('adminItemsUrl',url()->full());
        $category=$category->bySlug($slug);        
        $parent=$catalog->find($category->id);
        $subcategories = $category->subcategories($slug)->paginate(session()->get('subcategoriesPerPage'));
        $breadcrumb='catalogSubcategories';
        $item=$category;                
        return view('admin.partials.catalogs.subcategories.main', compact('subcategories','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, CatalogCategory $category)
    {
        $parent=$category->bySlug($slug);
        $breadcrumb='catalogSubcategories.create';
        $item=$parent;
        return view('admin.partials.catalogs.subcategories.form', compact('breadcrumb','parent','item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CatalogCategoryRequest $request, CatalogCategory $parent, $slug)
    {
        //
        $category = new CatalogCategory($request->all());
        $parent = $parent->bySlug($slug);
        $category->slug=str_slug($parent->slug.'-'.$category->title, "-");
        $category->parent=$parent->id;
        $category->catalog_id = $parent->catalog_id;
        $category->language = $parent->language;
        $category->image = hwImage()->widen($request, 'catalogCategory');        
        if ($category->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-subcategories', ['slug' => $parent->slug] ));
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
    public function edit(CatalogCategory $category, $slug)
    {
        //
        $category = $category->bySlug($slug);        
        $parent = $category->find($category->id);
        $breadcrumb='catalogSubcategories.edit';
        $item=$category;
        return view('admin.partials.catalogs.subcategories.form', compact('parent','category','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CatalogCategoryRequest $request, CatalogCategory $category, $slug)
    {
        //        
        $category=$category->bySlug($slug);
        $parent=$category->find($category->parent);
        $category->slug=str_slug($parent->slug.'-'.$request->title, "-");
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($category->image, 'catalogCategory');
            $category->image = hwImage()->widen($request, 'catalogCategory');            
        }
        if ($category->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-catalog-subcategories', ['slug' => $parent->slug] ));
    }

    /**
     * Move the page up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($slug, $parentId, $entityId)
    {
        //
        $entity = CatalogCategory::find($entityId);        
        $positionEntity = CatalogCategory::find($parentId);
        $entity->moveBefore($positionEntity);
        $parent=CatalogCategory::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-subcategories', ['slug' => $slug] ));
    }

    /**
     * Move the page down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($slug, $parentId,$entityId)
    {
        //
        $entity = CatalogCategory::find($entityId);
        $positionEntity = CatalogCategory::find($parentId);
        $entity->moveAfter($positionEntity);
        $parent=CatalogCategory::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-subcategories', ['slug' => $slug] ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CatalogCategory $category, $slug)
    {
        //
        $category=$category->bySlug($slug);
        event(new CatalogCategoryDelete($category));
        $parent=$category->where('id', $category->parent)->first();        
        hwImage()->destroy($category->image, 'catalogCategory');
        if ($category->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-catalog-subcategories', ['slug' => $parent->slug] ));
    }
}
