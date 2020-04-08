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
use App\Models\PostComment;
use App\Http\Requests\Admin\PostCommentRequest;
use Excel;
use JavaScript;
use URL;

class PostCommentsController extends Controller
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
        $comments = PostComment::orderBy('id', 'desc')->paginate(session()->get('commentsPerPage'));
        $breadcrumb='postComments';
        $request->session()->forget('newsPostCommentsUrl');
        return view('admin.partials.news.comments.main', compact('comments','breadcrumb'));
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
                         ->paginate(session()->get('commentsPerPage'));
        $breadcrumb='newsPosts';      
        return view('admin.partials.news.search', compact('posts', 'breadcrumb', 'search'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function post($slug, NewsPost $post, Request $request)
    {
        $post = $post->bySlug($slug);
        $comments = $post->comments;
        $breadcrumb='postComments';
        $request->session()->put('newsPostCommentsUrl', $request->fullUrl());
        return view('admin.partials.news.comments.postComments', compact('comments','breadcrumb', 'post'));
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
    public function edit(PostComment $comment, $id)
    {
        //
        $comment = $comment->find($id);
        $item = $comment;
        $breadcrumb='postComments.edit';
        return view('admin.partials.news.comments.form', compact('breadcrumb', 'item', 'comment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PostCommentRequest $request, PostComment $comment, $id)
    {
        // 
        $comment = $comment->find($id);
        if ($comment->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-news-comments.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PostComment $comment, $id, Request $request)
    {
        //
        $comment=$comment->find($id);        
        if ($comment->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));   
        redirect(route('admin-news-comments.index'));
    }
}
