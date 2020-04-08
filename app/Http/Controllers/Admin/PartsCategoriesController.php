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
use App\Models\Feed;
use App\Http\Requests\Admin\PartsCategoryRequest;
use App\Events\PartsCategoryDelete;
use App\Http\Libraries\PartCategoriesTree;
use App\Models\CarModelType;
use DB;
use JavaScript;
use URL;

class PartsCategoriesController extends Controller
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
        $categories = PartsCategory::whereParent(1)->sorted()->paginate(session()->get('categoriesPerPage'));
        $breadcrumb='partsCategories';
        return view('admin.partials.parts.categories.main', compact('categories','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        session()->put('adminItemsUrl',url()->full());
        $breadcrumb='partsCategories.create';
        return view('admin.partials.parts.categories.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartsCategoryRequest $request)
    {
        //
        $category = new PartsCategory($request->all());
        $category->slug = str_slug($category->title, "-");
        $category->parent = 0;
        $category->group = 1;
        $category->image = hwImage()->widen($request, 'partsCategory');        
        if ($category->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();
        return redirect(route('admin-parts-categories.index'));
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
        $breadcrumb='partsCategories.edit';
        $item=$category;
        return view('admin.partials.parts.categories.form', compact('category','breadcrumb','item'));
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
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($category->image, 'partsCategory');
            $category->image = hwImage()->widen($request, 'partsCategory');            
        }
        if ($category->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();
        //if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));  
        return redirect(route('admin-parts-categories.index'));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId, $entityId)
    {
        //
        $entity = PartsCategory::find($entityId);        
        $positionEntity = PartsCategory::find($parentId);
        $entity->moveBefore($positionEntity);
        $parent=PartsCategory::where('id', $entity->parent)->first();
        //if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-parts-categories.index'));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = PartsCategory::find($entityId);
        $positionEntity = PartsCategory::find($parentId);
        $entity->moveAfter($positionEntity);
        $parent=PartsCategory::where('id', $entity->parent)->first();
        //if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-parts-categories.index'));
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
        event(new PartsCategoryDelete($category));
        hwImage()->destroy($category->image, 'partsCategory');
        if ($category->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-parts-categories.index'));
    }

    public function searchCategories($slug, CarModelType $type)
    {   
        $type = $type->bySlug($slug);
        session()->put('admin-type', $type);
        $categories = collect(DB::select(DB::raw("SELECT DISTINCT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent>=1 AND type_categories.type_id=".$type->id." AND parts_categories.active = 'active' WHERE parts_categories.id != 72 ORDER BY position")));
        $breadcrumb = 'adminSearchPartsCategories';
        $item = $type;
        return view('admin.partials.parts.categories.searchCategories', compact('breadcrumb','categories','type', 'item'));
    }

    public function search($slug, CarModelType $type, Request $request)
    {   
        $type = $type->bySlug($slug);
        if($type->engines->count() > 0) {
            foreach($type->engines as $engine) {
                if(!empty($engine)) {
                    $engine = $engine->code;
                } else {
                    $engine = ' ';
                }    
            }
        } else {
            $engine = ' ';
        }       
        session()->put('admin-type', $type);
        $query = "SELECT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent>=1 AND type_categories.type_id=".$type->id." AND parts_categories.active = 'active' ";
        $searchItems = explode(' ', $request->search);
        $first = true;
        foreach ($searchItems as $key => $search) {
            if ($first == true)
            {
                $query .= " WHERE title LIKE '%".$search."%' OR terms LIKE '%".$search."%'";
                $first = false; 
            } else $query .= " OR title LIKE '%".$search."%' OR terms LIKE '%".$search."%'";            
        }
        $query .= " ORDER BY position";
        $categories = collect(DB::select(DB::raw($query)));
        $categories = new PartCategoriesTree($categories);
        $categories = $categories->buildTree();
        $breadcrumb = 'adminSearchPartsCategories';
        $item = $type;
        $search = $request->search;
        return view('admin.partials.parts.categories.search', compact('breadcrumb','categories','type', 'item', 'engine', 'search'));
    }
}
