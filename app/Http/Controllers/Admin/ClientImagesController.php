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
use App\Models\Client;
use App\Models\ClientImage;
use App\Http\Requests\Admin\ClientImageRequest;
use JavaScript;
use URL;

class ClientImagesController extends Controller
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
    public function index($slug, Client $client)
    {        
        session()->put('adminItemsUrl',url()->full());
        $item=$client->bySlug($slug);
        $images = $item->images($slug)->sorted()->paginate();
        $breadcrumb='clientImages';
        return view('admin.partials.clients.images.main', compact('images','breadcrumb','slug', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ClientImageRequest $request, Client $client, $slug)
    {        
        $client = $client->bySlug($slug);
        $image = new ClientImage($request->all());        
        $image->image = hwImage()->heighten($request, 'client');
        $image->active = 'active';    
        $image->title = $client->title.' '.$request->file('image')->getClientOriginalName();
        $image->client_id = $client->id;       
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
    public function edit($slug, Client $client, $id, ClientImage $image)
    {
        $parent=$client->bySlug($slug);
        $image=$image->find($id);
        $breadcrumb='clientImages.edit';
        $item=$image;
        return view('admin.partials.clients.images.form', compact('image','breadcrumb','item', 'parent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ClientImageRequest $request, $slug, $id, ClientImage $image)
    {
        //
        $image=$image->find($id);        
        if ($request->hasFile('image'))       
        {
            hwImage()->destroy($image->image,'client');
            $image->image = hwImage()->heighten($request, 'client');
        }
        if ($image->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route("admin-client-images", ['slug' => $image->client->slug]));
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
        $entity = ClientImage::find($entityId);
        $positionEntity = ClientImage::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-client-images", ['slug' => $entity->client->slug]));
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
        $entity = ClientImage::find($entityId);
        $positionEntity = ClientImage::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-client-images", ['slug' => $entity->client->slug]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, $id, ClientImage $image)
    {
        //
        $image=$image->find($id);
        hwImage()->destroy($image->image, 'client');
        if ($image->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route("admin-client-images", ['slug' => $slug]));
    }
}
