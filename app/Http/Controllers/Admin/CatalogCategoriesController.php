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
use App\Events\CatalogCategoryUpdate;
use JavaScript;
use URL;

class CatalogCategoriesController extends Controller
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
        $parent = Catalog::whereSlug($slug)->get()->first();
        $categories = $parent->categories()->whereParent(0)->sorted()->paginate(session()->get('categoriesPerPage'));
        $breadcrumb='catalogCategories';
        $item = $parent;        
        return view('admin.partials.catalogs.categories.main', compact('categories','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, Catalog $catalog)
    {
        $parent=$catalog->bySlug($slug);
        $breadcrumb='catalogCategories.create';
        $item=$parent;
        return view('admin.partials.catalogs.categories.form', compact('breadcrumb','parent','item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CatalogCategoryRequest $request, Catalog $catalog, $slug)
    {
        //
        $category = new CatalogCategory($request->all());
        $catalog = $catalog->bySlug($slug);
        $category->slug=str_slug($catalog->title.'-'.$category->title, "-");
        $category->parent=0;
        $category->catalog_id = $catalog->id;
        $category->language = $catalog->language;
        $category->image = hwImage()->widen($request, 'catalogCategory');        
        if ($category->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-categories.index', ['catalogSlug' => $catalog->slug] ));
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
    public function edit(Catalog $catalog, $catalogSlug, CatalogCategory $category, $categorySlug)
    {
        //
        $category = $category->bySlug($categorySlug);        
        $parent = $catalog->bySlug($catalogSlug);
        $breadcrumb='catalogCategories.edit';
        $item=$category;
        return view('admin.partials.catalogs.categories.form', compact('parent','category','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CatalogCategoryRequest $request, Catalog $catalog, $catalogSlug, CatalogCategory $category, $categorySlug)
    {
        //        
        $category=$category->bySlug($categorySlug);
        $parent=$category->catalog;
        $category->slug=str_slug($parent->title.'-'.$request->title, "-");
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($category->image, 'catalogCategory');
            $category->image = hwImage()->widen($request, 'catalogCategory');            
        }
        if ($category->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        event(new CatalogCategoryUpdate($category));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-catalog-categories.index', ['catalogSlug' => $catalogSlug] ));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($catalogSlug, $parentId, $entityId)
    {
        //
        $entity = CatalogCategory::find($entityId);        
        $positionEntity = CatalogCategory::find($parentId);
        $entity->moveBefore($positionEntity);
        $parent=CatalogCategory::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-categories.index', ['catalogSlug' => $catalogSlug] ));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($catalogSlug, $parentId,$entityId)
    {
        //
        $entity = CatalogCategory::find($entityId);
        $positionEntity = CatalogCategory::find($parentId);
        $entity->moveAfter($positionEntity);
        $parent=CatalogCategory::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-categories.index', ['catalogSlug' => $catalogSlug] ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $catalogSlug, CatalogCategory $category, $categorySlug)
    {
        //
        $category=$category->bySlug($categorySlug);
        event(new CatalogCategoryDelete($category));
        $parent=$category->where('id', $category->parent)->first();
        hwImage()->destroy($category->image, 'catalogCategory');
        if ($category->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-catalog-categories.index', ['catalogSlug' => $catalogSlug] ));
    }
}
