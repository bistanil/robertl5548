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
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Currency;
use App\Models\Client;
use Excel;
use JavaScript;
use URL;

class WishlistsController extends Controller
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
        $wishlists = Wishlist::where('total', '>', 0)->orderBy('id', 'desc')->paginate();
        $defaultCurrency = Currency::whereDefault('yes')->get()->first();
        $breadcrumb='wishlists';
        $request->session()->forget('clientWishlistsUrl');
        return view('admin.partials.wishlists.main', compact('wishlists', 'breadcrumb', 'defaultCurrency'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function clientWishlists($slug, Client $client, Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        $client = $client->bySlug($slug);
        $wishlists = Wishlist::whereClientId($client->id)->paginate();
        $defaultCurrency = Currency::whereDefault('yes')->get()->first();
        $breadcrumb='wishlists';        
        $request->session()->put('clientWishlistUrl', $request->fullUrl());
        return view('admin.partials.wishlists.main', compact('client','breadcrumb', 'wishlists', 'defaultCurrency'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Wishlist $wishlist)
    {
        $wishlist = $wishlist->find($id);
        $defaultCurrency = Currency::whereDefault('yes')->get()->first();
        $breadcrumb='wishlists.show';
        $item = $wishlist;
        return view('admin.partials.wishlists.show', compact('wishlist', 'breadcrumb', 'defaultCurrency', 'item'));
    }

}
