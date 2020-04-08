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
use App\Models\ManufacturerImage;
use App\Http\Requests\Admin\ManufacturerImageRequest;
use JavaScript;
use URL;

class ManufacturerImagesController extends Controller
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
    public function index($slug, Manufacturer $manufacturer)
    {
        session()->put('adminItemsUrl',url()->full());
        $item=$manufacturer->bySlug($slug);
        $images = $manufacturer->images($slug)->sorted()->paginate();        
        $breadcrumb='manufacturerImages';
        return view('admin.partials.manufacturers.images.main', compact('images','breadcrumb','slug', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ManufacturerImageRequest $request, Manufacturer $manufacturer, $slug)
    {        
        $manufacturer = $manufacturer->bySlug($slug);
        $image = new ManufacturerImage($request->all());        
        $image->image = hwImage()->heighten($request, 'manufacturer');    
        $image->active = 'active';    
        $image->title = $manufacturer->title.' '.$request->file('image')->getClientOriginalName();
        $image->manufacturer_id = $manufacturer->id;
        $image->save();
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
    public function edit($slug, Manufacturer $manufacturer, $id, ManufacturerImage $image)
    {
        $parent=$manufacturer->bySlug($slug);
        $image=$image->find($id);
        $breadcrumb='manufacturerImages.edit';
        $item=$image;
        return view('admin.partials.manufacturers.images.form', compact('image','breadcrumb','item', 'parent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ManufacturerImageRequest $request, $slug, $id, ManufacturerImage $image)
    {
        //
        $image=$image->find($id);        
        if ($request->hasFile('image'))       
        {
            hwImage()->destroy($image->image,'manufacturer');
            $image->image = hwImage()->heighten($request, 'manufacturer');
        }
        if ($image->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route("admin-manufacturer-images", ['slug' => $image->manufacturer->slug]));
    }

    /**
     * Move the image up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */    
    public function sortUp($parentId,$entityId)
    {    
        //
        $entity = ManufacturerImage::find($entityId);
        $positionEntity = ManufacturerImage::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-manufacturer-images", ['slug' => $entity->manufacturer->slug]));
    }

    /**
     * Move the image down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = ManufacturerImage::find($entityId);
        $positionEntity = ManufacturerImage::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-manufacturer-images", ['slug' => $entity->manufacturer->slug]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, $id, ManufacturerImage $image)
    {
        //
        $image=$image->find($id);
        hwImage()->destroy($image->image, 'manufacturer');
        if ($image->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route("admin-manufacturer-images", ['slug' => $slug]));
    }
}
