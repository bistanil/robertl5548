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
use App\Http\Libraries\CarConnections;
use App\Models\Manufacturer;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\Currency;
use App\Models\PartsCategory;
use App\Models\CategoryPart;
use App\Models\ProductCategory;
use App\Models\Car;
use App\Models\CarModelGroup;
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Models\CarPart;
use App\Models\ModelPart;
use App\Models\TypePart;
use App\Models\PartCode;
use App\Models\TypeCategory;
use App\Http\Requests\Admin\PackageRequest;
use App\Http\Requests\Admin\ExcelImportRequest;
use App\Events\PackageDelete;
use App\Models\Supplier;
use Excel;
use Artisan;
use Symfony\Component\Process\Process as Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Mail;
use JavaScript;
use URL;
use Part;

class PackagesController extends Controller
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
        $products = CatalogProduct::where('type','=', 'kit')->paginate();
        $breadcrumb='packages';
        return view('admin.partials.parts.newParts.main', compact('products','breadcrumb'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function category($slug, CatalogCategory $category, Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        $parent=$category->bySlug($slug);
        $products = $parent->products()->paginate();
        $breadcrumb='catalogProducts';
        $item=$parent; 
        $request->session()->flash('categoryProductsUrl',$request->fullUrl());
        $request->session()->keep('categoryProductsUrl');        
        return view('admin.partials.parts.catalogs.products.category', compact('products','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = PartsCategory::whereGroup(1)->sorted()->get();
        $currencies=Currency::all();
        $productCategories = [];
        $manufacturers=Manufacturer::sorted()->get();
        $suppliers = Supplier::all();
        $breadcrumb='packages.create';
        return view('admin.partials.parts.newParts.form', compact('breadcrumb', 'categories', 'manufacturers', 'currencies', 'productCategories', 'cars', 'productCars','suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PackageRequest $request)
    {
        $product = new CatalogProduct($request->all());        
        $manufacturer = Manufacturer::find($request->manufacturer_id);
        $product->slug = str_slug($product->title.'-'.$product->code, "-");
        $info = $request->all();
        $info['catalog_id'] = 0;
        if (Part::store($info)) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if ($request->session()->has('categoryProductsUrl')) return redirect($request->session()->pull('categoryProductsUrl'));
        return redirect(route('admin-packages.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug, CatalogProduct $part, Request $request)
    {
        $request->session()->flash('categoryProductsUrl',$request->fullUrl());
        $request->session()->keep('categoryProductsUrl');
        $part = $part->bySlug($slug);
        $breadcrumb = 'parts.details';
        $item = $part;
        return view('admin.partials.parts.parts.part', compact('breadcrumb', 'part', 'item'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        //
        if (Session::has('backUrl')) Session::keep('backUrl');
        $product = CatalogProduct::whereSlug($slug)->get()->first();
        $categories = PartsCategory::whereGroup(1)->sorted()->get();
        $currencies = Currency::all();        
        $breadcrumb = 'packages.edit';
        $item = $product;
        $productPrice = ProductPrice::where('product_id', $product->id)->first();
        $productCategories = CategoryPart::wherePart_id($product->id)->pluck('category_id')->toArray();
        $suppliers = Supplier::all(); 
        $manufacturers = Manufacturer::sorted()->get();
        return view('admin.partials.parts.newParts.form', compact('breadcrumb','item', 'currencies', 'product', 'productPrice', 'categories', 'productCategories', 'manufacturers','suppliers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PackageRequest $request, CatalogProduct $product, $slug)
    {
        // 
        $product = CatalogProduct::whereSlug($slug)->get->first();
        if (Part::update($request->all(), $product)) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if (Session::has('backUrl')) return redirect(Session::get('backUrl'));
        if ($request->session()->has('categoryPartsUrl')) return redirect($request->session()->pull('categoryPartsUrl'));
        return redirect(route('admin-packages.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CatalogProduct $product, $slug, Request $request)
    {
        //
        $product=$product->bySlug($slug);        
        event(new PackageDelete($product));
        if ($product->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));   
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if ($request->session()->has('categoryPartsUrl')) return redirect($request->session()->pull('categoryPartsUrl'));  
        return redirect(route('admin-packages.index'));
    }

}