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
use App\Http\Requests\Admin\CatalogRequest;
use App\Events\CatalogDelete;
use App\Events\CatalogUpdate;
use JavaScript;
use URL;

class CatalogsController extends Controller
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
        session()->put('adminItemsUrl',url()->full());
        $catalogs = Catalog::sorted()->paginate();
        $breadcrumb='catalogs';
        return view('admin.partials.catalogs.main', compact('catalogs','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='catalog.create';
        return view('admin.partials.catalogs.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CatalogRequest $request)
    {
        //
        $catalog = new Catalog($request->all());
        $catalog->slug=str_slug($catalog->title, "-");
        $catalog->image = hwImage()->widen($request, 'catalog');
        if ($catalog->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-catalogs');
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
    public function edit($slug, Catalog $catalog)
    {
        //
        $catalog = $catalog->bySlug($slug);
        $breadcrumb='catalog.edit';
        $item=$catalog;
        return view('admin.partials.catalogs.form', compact('catalog','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CatalogRequest $request, $slug, Catalog $catalog)
    {
        //
        $catalog=$catalog->bySlug($slug);
        $catalog->slug=str_slug($request->title, "-");        
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($catalog->image, 'catalog');
            $catalog->image = hwImage()->widen($request, 'catalog');
        }
        if ($catalog->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        event(new CatalogUpdate($catalog));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect('admin-catalogs');
    }

    /**
     * Move the catalog up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId,$entityId)
    {
        //
        $entity = Catalog::find($entityId);
        $positionEntity = Catalog::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-catalogs');
    }

    /**
     * Move the catalog down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = Catalog::find($entityId);
        $positionEntity = Catalog::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-catalogs');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, Catalog $catalog)
    {
        //
        $catalog=$catalog->bySlug($slug); 
        hwImage()->destroy($catalog->image, 'catalog');       
        event(new CatalogDelete($catalog));
        if ($catalog->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-catalogs');
    }
}
