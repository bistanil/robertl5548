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
use App\Models\CatalogAttribute;
use App\Http\Requests\Admin\CatalogAttributeRequest;
use App\Events\CatalogAttributeDelete;
use JavaScript;
use URL;

class CatalogAttributesController extends Controller
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
        $parent = $catalog->bySlug($slug);
        $attributes = $parent->attributes($slug)->sorted()->paginate(session()->get('attributesPerPage'));
        $breadcrumb='catalogAttributes';
        $item=$parent;        
        return view('admin.partials.catalogs.attributes.main', compact('attributes','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, Catalog $catalog)
    {
        $parent = $catalog->bySlug($slug);
        $breadcrumb = 'catalogAttributes.create';
        $item = $parent;
        $lists = $item->lists($slug)->get();
        return view('admin.partials.catalogs.attributes.form', compact('breadcrumb','parent','item', 'lists'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CatalogAttributeRequest $request, Catalog $catalog, $slug)
    {
        //
        $attribute = new CatalogAttribute($request->all());
        $catalog = $catalog->bySlug($slug);
        $attribute->catalog_id = $catalog->id;
        $attribute->language = $catalog->language;
        if ($attribute->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-attributes.index', ['catalogSlug' => $catalog->slug] ));
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
    public function edit(Catalog $catalog, $slug, CatalogAttribute $attribute, $attributeId)
    {
        //
        $attribute = $attribute->find($attributeId);        
        $parent = $catalog->bySlug($slug);
        $lists=$parent->lists($slug)->get();
        $breadcrumb='catalogAttributes.edit';
        $item=$attribute;
        return view('admin.partials.catalogs.attributes.form', compact('parent','attribute','breadcrumb','item', 'lists'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CatalogAttributeRequest $request, Catalog $catalog, $slug, CatalogAttribute $attribute, $attributeId)
    {
        //        
        $attribute=$attribute->find($attributeId);
        $parent=$attribute->catalog;
        $input=$request->all();
        if ($input['is_list']!='yes') unset($input['list_id']);
        if ($attribute->update($input)) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-catalog-attributes.index', ['catalogSlug' => $slug] ));
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
        $entity = CatalogAttribute::find($entityId);        
        $positionEntity = CatalogAttribute::find($parentId);
        $entity->moveBefore($positionEntity);
        $parent=CatalogAttribute::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-attributes.index', ['catalogSlug' => $catalogSlug] ));
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
        $entity = CatalogAttribute::find($entityId);
        $positionEntity = CatalogAttribute::find($parentId);
        $entity->moveAfter($positionEntity);
        $parent=CatalogAttribute::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-attributes.index', ['catalogSlug' => $catalogSlug] ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $catalogSlug, CatalogAttribute $attribute, $id)
    {
        //
        $attribute=$attribute->find($id);
        event(new CatalogAttributeDelete($attribute));
        if ($attribute->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-catalog-attributes.index', ['catalogSlug' => $catalogSlug] ));
    }
}
