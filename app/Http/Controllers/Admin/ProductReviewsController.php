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
use App\Models\ProductReview;
use App\Http\Requests\Admin\ProductReviewRequest;
use Excel;
use JavaScript;
use URL;

class ProductReviewsController extends Controller
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
        $reviews = ProductReview::orderBy('id', 'desc')->paginate(session()->get('reviewsPerPage'));
        $breadcrumb='productReviews';
        $request->session()->forget('productReviewsUrl');
        return view('admin.partials.catalogs.products.reviews.main', compact('reviews','breadcrumb'));
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
                         ->paginate(session()->get('reviewsPerPage'));
        $breadcrumb='newsPosts';      
        return view('admin.partials.news.search', compact('posts', 'breadcrumb', 'search'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function productReviews($slug, CatalogProduct $product, Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        $product = $product->bySlug($slug);
        $reviews = ProductReview::whereProduct_id($product->id)->orderBy('id', 'desc')->paginate(session()->get('reviewsPerPage'));
        $breadcrumb='productReviews';
        $request->session()->put('productReviewsUrl', $request->fullUrl());
        return view('admin.partials.catalogs.products.reviews.productReviews', compact('reviews','breadcrumb', 'product'));
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
    public function edit(ProductReview $review, $id)
    {
        //
        $review = $review->find($id);
        $item = $review;
        $breadcrumb='productReviews.edit';
        return view('admin.partials.catalogs.products.reviews.form', compact('breadcrumb', 'item', 'review'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductReviewRequest $request, ProductReview $review, $id)
    {
        // 
        $review = $review->find($id);
        if ($review->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-product-reviews.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductReview $reviews, $id, Request $request)
    {
        //
        $reviews=$reviews->find($id);        
        if ($reviews->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));   
        return redirect(route('admin-product-reviews.index'));
    }
}