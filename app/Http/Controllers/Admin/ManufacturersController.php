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
use App\Models\Manufacturer;
use App\Http\Requests\Admin\ManufacturerRequest;
use App\Events\ManufacturerDelete;
use JavaScript;
use URL;

class ManufacturersController extends Controller
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
        $manufacturers = Manufacturer::sorted()->paginate(session()->get('manufacturersPerPage'));
        $breadcrumb='manufacturers';
        return view('admin.partials.manufacturers.main', compact('manufacturers','breadcrumb'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('manufacturerSearch',$request->q);
        $request->session()->keep('manufacturerSearch');         
        $search = $request->session()->get('manufacturerSearch');
        $manufacturers = Manufacturer::where('manufacturers.title', 'LIKE', "%$search%")
                          ->orWhere('manufacturers.content', 'LIKE', "%$search%")                          
                          ->paginate(session()->get('manufacturersPerPage'));
        $breadcrumb='manufacturers';
        return view('admin.partials.manufacturers.search', compact('manufacturers', 'breadcrumb', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='manufacturer.create';
        return view('admin.partials.manufacturers.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ManufacturerRequest $request)
    {
        //
        $manufacturer = new Manufacturer($request->all());
        $manufacturer->slug=str_slug($manufacturer->title, "-");
        if ($manufacturer->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-manufacturers');
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
    public function edit($slug, Manufacturer $manufacturer)
    {
        //
        $manufacturer = $manufacturer->bySlug($slug);
        $breadcrumb='manufacturer.edit';
        $item=$manufacturer;
        return view('admin.partials.manufacturers.form', compact('manufacturer','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ManufacturerRequest $request, $slug, Manufacturer $manufacturer)
    {
        //
        $manufacturer=$manufacturer->bySlug($slug);
        $manufacturer->slug=str_slug($manufacturer->title, "-");
        if ($manufacturer->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect('admin-manufacturers');
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId,$entityId)
    {
        //
        $entity = Manufacturer::find($entityId);
        $positionEntity = Manufacturer::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-manufacturers');
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
        $entity = Manufacturer::find($entityId);
        $positionEntity = Manufacturer::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-manufacturers');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, Manufacturer $manufacturer)
    {
        //
        $manufacturer=$manufacturer->bySlug($slug);        
        event(new ManufacturerDelete($manufacturer));
        if ($manufacturer->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-manufacturers');
    }
}
