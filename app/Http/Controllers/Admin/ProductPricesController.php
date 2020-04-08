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
use App\Http\Libraries\Price;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\Currency;
use App\Models\Supplier;
use App\Http\Requests\Admin\ProductPriceRequest;
use JavaScript;
use URL;
use ProdPrice;

class ProductPricesController extends Controller
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
        $prices = $item->prices($slug)->get();
        $breadcrumb ='productPrices';
        return view('admin.partials.parts.prices.main', compact('prices','breadcrumb','slug', 'item'));
    }

    public function create($slug)
    {
        $item = CatalogProduct::whereSlug($slug)->get()->first();
        $currencies = Currency::all();
        $suppliers = Supplier::all(); 
        $breadcrumb='productPrices.create';
        return view('admin.partials.parts.prices.form', compact('breadcrumb', 'item', 'currencies','suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductPriceRequest $request, $slug)
    {        
        $product = CatalogProduct::whereSlug($slug)->get()->first();
        if (ProdPrice::store($request->toArray(), $product)) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route("admin-product-prices", ['slug' => $price->product->slug]));
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
        $price = ProductPrice::find($id);
        $currencies = Currency::all();
        $suppliers = Supplier::all(); 
        $breadcrumb = 'productPrices.edit';
        $item = $price;
        return view('admin.partials.parts.prices.form', compact('price','breadcrumb','item', 'parent', 'currencies','suppliers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductPriceRequest $request, $slug, $id)
    {
        //
        $price = ProductPrice::find($id);        
        if (ProdPrice::update($request->all(), $price->product)) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));        
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));       
        return redirect(route("admin-product-prices", ['slug' => $price->product->slug]));
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
        $price = ProductPrice::find($id);
        if ($price->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText')); 
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));    
        return redirect(route("admin-product-prices", ['slug' => $slug]));
    }
}