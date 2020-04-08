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
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\PostCategory;
use App\Http\Requests\Admin\NewsPostRequest;
use App\Events\NewsPostDelete;
use Excel;
use JavaScript;
use URL;

class NewsPostsController extends Controller
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
    public function index(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        $posts = NewsPost::orderBy('id', 'desc')->paginate(session()->get('newsPerPage'));
        $breadcrumb='newsPosts';
        $request->session()->forget('newsCategoryUrl');
        return view('admin.partials.news.main', compact('posts','breadcrumb'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('newsPostSearch',$request->q);
        $request->session()->keep('newsPostSearch');         
        $search = $request->session()->get('newsPostSearch');
        $posts = NewsPost::orWhere('news_posts.title', 'LIKE', "%$search%")
                         ->orWhere('news_posts.content', 'LIKE', "%$search%")                           
                         ->paginate(session()->get('newsPerPage'));
        $breadcrumb='newsPosts';      
        return view('admin.partials.news.search', compact('posts', 'breadcrumb', 'search'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function categoryNews($slug, NewsCategory $category, Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        $parent=$category->bySlug($slug);
        $posts = $parent->posts()->paginate();
        $breadcrumb='newsCategoryPosts';
        $item=$parent; 
        $request->session()->flash('categoryPostsUrl',$request->fullUrl());
        $request->session()->keep('categoryPostsUrl');        
        return view('admin.partials.news.categoryPosts', compact('posts','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories=NewsCategory::orderBy('id','desc')->get();
        $breadcrumb='newsPosts.create';
        return view('admin.partials.news.form', compact('breadcrumb', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NewsPostRequest $request)
    {
        //        
        $post = new NewsPost($request->all());
        $post->slug = str_slug($post->title, "-");        
        if ($post->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        foreach ($request->categories as $key => $categoryId) {
            $category = new PostCategory();
            $category->category_id=$categoryId;
            $category->post_id=$post->id;
            $category->save();
        }
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if ($request->session()->has('categoryPostsUrl')) return redirect($request->session()->pull('categoryPostsUrl'));
        return redirect(route('admin-news-posts.index'));
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
    public function edit(NewsPost $post, $slug)
    {
        //
        $post = $post->bySlug($slug);
        $categories=NewsCategory::orderBy('id', 'desc')->get();
        $breadcrumb='newsPosts.edit';
        $item=$post;
        $postCategories=PostCategory::where('post_id', $post->id)->pluck('category_id')->toArray();
        return view('admin.partials.news.form', compact('breadcrumb', 'item', 'categories', 'post', 'postCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(NewsPostRequest $request, NewsPost $post, $slug)
    {
        // 
        $post = $post->bySlug($slug);
        $post->slug = str_slug($request->title, "-");
        if ($post->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        PostCategory::where('post_id', $post->id)->delete();
        $categories=$request->input('categories');
        foreach ($categories as $key => $categoryId) {
            $category = new PostCategory();
            $category->category_id=$categoryId;
            $category->post_id=$post->id;
            $category->save();
        }
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if ($request->session()->has('categoryPostsUrl')) return redirect($request->session()->pull('categoryPostsUrl'));
        return redirect(route('admin-news-posts.index'));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function categorySortUp($slug, NewsCategory $category, $parentId, $entityId)
    {
        //
        $category=$category->bySlug($slug);        
        $entity = NewsCategory::where('category_id', $category->id)->where('post_id', $entityId)->first();
        $positionEntity = PostCategory::where('category_id', $category->id)->where('post_id', $parentId)->first();
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-news-posts.index'));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function categorySortDown($slug, NewsCategory $category, $parentId,$entityId)
    {
        //
        $category=$category->bySlug($slug);
        $entity = PostCategory::where('category_id', $category->id)->where('post_id', $entityId)->first();
        $positionEntity = PostCategory::where('category_id', $category->id)->where('post_id', $parentId)->first();
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-news-posts.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewsPost $post, $slug, Request $request)
    {
        //
        $post=$post->bySlug($slug);        
        event(new NewsPostDelete($post));
        if ($post->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));   
        if ($request->session()->has('categoryPostsUrl')) return redirect($request->session()->pull('categoryPostsUrl'));  
        return redirect(route('admin-news-posts.index'));
    }
}
