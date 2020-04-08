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
use App\Models\PartsCategory;
use App\Http\Requests\Admin\PartsCategoryRequest;
use App\Events\PartsCategoryDelete;
use JavaScript;
use URL;

class PartsSubcategoriesController extends Controller
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
    public function index($slug, PartsCategory $category)
    {
        session()->put('adminItemsUrl',url()->full());
        $item = $category->bySlug($slug);
        $categories = PartsCategory::whereParent($item->id)->sorted()->paginate(session()->get('categoriesPerPage'));
        $breadcrumb='partsSubcategories';
        return view('admin.partials.parts.categories.subcategories.main', compact('categories','breadcrumb', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, PartsCategory $category)
    {
        $item = $category->bySlug($slug);
        $breadcrumb = 'partsSubcategories.create';
        return view('admin.partials.parts.categories.subcategories.form', compact('breadcrumb', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartsCategoryRequest $request, $slug, PartsCategory $parentCategory)
    {
        //
        $parentCategory = $parentCategory->bySlug($slug);
        $category = new PartsCategory($request->all());
        $category->slug=str_slug($category->title, "-");
        $category->language = $parentCategory->language;
        $category->parent = $parentCategory->id;
        $category->group = 1;
        $category->image = hwImage()->widen($request, 'partsCategory');        
        if ($category->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();
        //if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-parts-subcategories', ['slug' => $slug]));
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
    public function edit(PartsCategory $category, $slug)
    {
        //
        $category = $category->bySlug($slug);
        $parent = $category->find($category->parent);        
        $breadcrumb='partsSubcategories.edit';
        $item=$category;
        return view('admin.partials.parts.categories.subcategories.form', compact('category','breadcrumb','item', 'parent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PartsCategoryRequest $request, PartsCategory $category, $slug)
    {
        //        
        $category=$category->bySlug($slug);
        $parent = $category->find($category->parent);
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($category->image, 'partsCategory');
            $category->image = hwImage()->widen($request, 'partsCategory');            
        }
        if ($category->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back(); 
        //if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));    
        return redirect(route('admin-parts-subcategories', ['slug' => $parent->slug]));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($slug, $parentId, $entityId)
    {
        //
        $entity = PartsCategory::find($entityId);        
        $positionEntity = PartsCategory::find($parentId);
        $entity->moveBefore($positionEntity);
        $parent=PartsCategory::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-parts-subcategories', ['slug' => $slug]));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($slug, $parentId,$entityId)
    {
        //
        $entity = PartsCategory::find($entityId);
        $positionEntity = PartsCategory::find($parentId);
        $entity->moveAfter($positionEntity);
        $parent=PartsCategory::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-parts-subcategories', ['slug' => $slug]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PartsCategory $category, $slug)
    {
        //
        $category=$category->bySlug($slug);
        $parent = $category->find($category->parent);
        event(new PartsCategoryDelete($category));
        hwImage()->destroy($category->image, 'partsCategory');
        if ($category->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-parts-subcategories', ['slug' => $parent->slug]));
    }
}
