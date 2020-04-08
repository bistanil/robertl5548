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
use App\Models\CatalogList;
use App\Models\CatalogListItem;
use App\Http\Requests\Admin\CatalogListItemRequest;
use App\Http\Requests\Admin\ExcelImportRequest;
use Excel;
use Artisan;
use JavaScript;
use URL;

class CatalogListItemsController extends Controller
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
    public function index($slug, CatalogList $list)
    {
        session()->put('adminItemsUrl',url()->full());
        $parent=$list->bySlug($slug);
        $items = $list->items($parent->id)->sorted()->paginate(session()->get('listItemsPerPage'));
        $breadcrumb='catalogListItems';
        $item=$parent;        
        return view('admin.partials.catalogs.listItems.main', compact('items','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, CatalogList $list)
    {
        $parent=$list->bySlug($slug);
        $breadcrumb='catalogListItems.create';
        $item=$parent;
        return view('admin.partials.catalogs.listItems.form', compact('breadcrumb','parent','item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CatalogListItemRequest $request, CatalogList $list, $slug)
    {
        //
        $item = new CatalogListItem($request->all());
        $list = $list->bySlug($slug);
        $item->list_id = $list->id;
        if ($item->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-list-items.index', ['slug' => $list->slug] ));
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
    public function edit(CatalogList $list, $slug, CatalogListItem $item, $itemId)
    {
        //
        $parent = $list->bySlug($slug);        
        $listItem = $item->find($itemId);
        $item=$listItem;
        $breadcrumb='catalogListItems.edit';        
        return view('admin.partials.catalogs.listItems.form', compact('parent','breadcrumb','listItem','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CatalogListItemRequest $request, $slug, CatalogListItem $item, $itemId)
    {
        //        
        $item=$item->find($itemId);
        if ($item->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-catalog-list-items.index', ['slug' => $slug] ));
    }

     /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */

      /**
     * Display Excel import form.
     *
     * @return \Illuminate\Http\Response
     */
    public function excelImportForm($slug, CatalogList $list)
    {
        $item=$list->bySlug($slug);
        $breadcrumb='catalogListItems.import';         
        return view('admin.partials.catalogs.listItems.importForm', compact('breadcrumb','item'));
    }

    /**
     * Import products from Excel file
     *
     * @return \Illuminate\Http\Response
     */
    public function excelImport($slug, CatalogList $list, ExcelImportRequest $request)
    {
        $request->file('excel')->move('public/files/import/', 'listItemsImport.xlsx');
        $list=$list->bySlug($slug);
        chdir(base_path());
        exec('bash -c "exec nohup setsid php artisan import-list-items-excel '.$list->id.' > /dev/null 2>&1 &"');
        if ($list->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-catalog-list-items.index', ['slug' => $slug] ));
    }

    public function sortUp($slug, $parentId, $entityId)
    {
        //
        $entity = CatalogListItem::find($entityId);        
        $positionEntity = CatalogListItem::find($parentId);
        $entity->moveBefore($positionEntity);
        $parent=CatalogListItem::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-list-items.index', ['slug' => $slug] ));
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
        $entity = CatalogListItem::find($entityId);
        $positionEntity = CatalogListItem::find($parentId);
        $entity->moveAfter($positionEntity);
        $parent=CatalogListItem::where('id', $entity->parent)->first();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-list-items.index', ['slug' => $slug] ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $slug, CatalogListItem $item, $itemId)
    {
        //
        $item=$item->find($itemId);        
        if ($item->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-catalog-list-items.index', ['slug' => $slug] ));
    }
}