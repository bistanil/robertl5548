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
use App\Models\CatalogProduct;
use App\Models\ProductImage;
use App\Http\Requests\Admin\ProductImageRequest;
use JavaScript;
use URL;

class PartImagesController extends Controller
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
    public function index($slug)
    {     
        session()->put('adminItemsUrl',url()->full());   
        $item = CatalogProduct::whereSlug($slug)->get()->first();
        $images = $item->images($slug)->sorted()->paginate();
        $breadcrumb = 'partImages';
        return view('admin.partials.parts.images.main', compact('images','breadcrumb','slug', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductImageRequest $request, $slug)
    {        
        $product = CatalogProduct::whereSlug($slug)->get()->first();
        $image = new ProductImage($request->all());        
        $image->image = hwImage()->heighten($request, 'product');    
        $image->active = 'active';    
        $image->title = $product->title.' '.$request->file('image')->getClientOriginalName();
        $image->product_id = $product->id;
        $image->source = 'part';
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
    public function edit($slug, $id)
    {
        $parent = CatalogProduct::whereSlug($slug)->get()->first();
        $image = ProductImage::find($id);
        $breadcrumb = 'partImages.edit';
        $item = $image;
        return view('admin.partials.parts.images.form', compact('image','breadcrumb','item', 'parent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductImageRequest $request, $slug, $id)
    {
        //
        $image = ProductImage::find($id);        
        if ($request->hasFile('image'))       
        {
            hwImage()->destroy($image->image,'product');
            $image->image = hwImage()->heighten($request, 'product');
        }
        if ($image->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));       
        return redirect(route("admin-part-images", ['slug' => $image->product->slug]));
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
        $entity = ProductImage::find($entityId);
        $positionEntity = ProductImage::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-part-images", ['slug' => $entity->product->slug]));
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
        $entity = ProductImage::find($entityId);
        $positionEntity = ProductImage::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-part-images", ['slug' => $entity->product->slug]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, $id)
    {
        //
        $image = ProductImage::find($id);
        hwImage()->destroy($image->image, 'product');
        if ($image->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText')); 
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));    
        return redirect(route("admin-part-images", ['slug' => $slug]));
    }
}
