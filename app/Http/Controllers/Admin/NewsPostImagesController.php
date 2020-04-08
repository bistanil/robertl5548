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
use App\Models\NewsPost;
use App\Models\PostImage;
use App\Http\Requests\Admin\PostImageRequest;
use JavaScript;
use URL;

class NewsPostImagesController extends Controller
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
    public function index($slug, NewsPost $post)
    {
        session()->put('adminItemsUrl',url()->full());        
        $item=$post->bySlug($slug);
        $images = $item->images($slug)->sorted()->paginate();
        $breadcrumb='newsPostImages';
        return view('admin.partials.news.images.main', compact('images','breadcrumb','slug', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostImageRequest $request, NewsPost $post, $slug)
    {        
        $post = $post->bySlug($slug);
        $image = new PostImage($request->all());        
        $image->image = hwImage()->heighten($request, 'newsPost');    
        $image->active = 'active';    
        $image->title = $post->title.' '.$request->file('image')->getClientOriginalName();
        $image->post_id = $post->id;
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
    public function edit($slug, NewsPost $post, $id, PostImage $image)
    {
        $parent=$post->bySlug($slug);
        $image=$image->find($id);
        $breadcrumb='newsPostImages.edit';
        $item=$image;
        return view('admin.partials.news.images.form', compact('image','breadcrumb','item', 'parent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PostImageRequest $request, $slug, $id, PostImage $image)
    {
        //
        $image=$image->find($id);        
        if ($request->hasFile('image'))       
        {
            hwImage()->destroy($image->image,'newsPost');
            $image->image = hwImage()->heighten($request, 'newsPost');
        }
        if ($image->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route("admin-news-post-images", ['slug' => $image->post->slug]));
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
        $entity = PostImage::find($entityId);
        $positionEntity = PostImage::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-news-post-images", ['slug' => $entity->post->slug]));
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
        $entity = PostImage::find($entityId);
        $positionEntity = PostImage::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-news-post-images", ['slug' => $entity->post->slug]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, $id, PostImage $image)
    {
        //
        $image=$image->find($id);
        hwImage()->destroy($image->image, 'newsPost');
        if ($image->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route("admin-news-post-images", ['slug' => $slug]));
    }
}
