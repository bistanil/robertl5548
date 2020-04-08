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
use App\Models\Car;
use App\Models\CarModelGroup;
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Models\CarPart;
use App\Models\ModelPart;
use App\Models\TypePart;
use App\Models\PartCode;
use App\Models\TypeCategory;
use App\Models\Supplier;
use App\Http\Requests\Admin\CatalogProductRequest;
use App\Http\Requests\Admin\ExcelImportRequest;
use App\Http\Requests\Admin\ExcelWithSupplierImportRequest;
use App\Events\PartDelete;
use App\Http\Libraries\CopyPart;
use App\Http\Libraries\PartConnections;
use App\Models\Feed;
use Excel;
use Artisan;
use Symfony\Component\Process\Process as Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Mail;
use JavaScript;
use URL;
use DB;
use Part;

class PartsController extends Controller
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
        $products = CatalogProduct::where('catalog_id','=',0)->paginate(session()->get('partsPerPage'));
        $breadcrumb='parts';
        return view('admin.partials.parts.mains.main', compact('products','breadcrumb'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->put('partCodeSearch',$request->q);
        $search = session()->get('partCodeSearch');
        $products = CatalogProduct::join('part_codes', function ($join) use ($search) { 
                                            $join->on('catalog_products.id', '=', 'part_codes.part_id')
                                                 ->where('part_codes.code', 'LIKE', preg_replace("/[^a-zA-Z0-9]+/","",$search));
                                    })
                                ->leftJoin(DB::raw('(SELECT product_id, min(price) as price FROM `catalog_price_product` group by product_id) as prices'), function ($join) { 
                                            $join->on('catalog_products.id', '=', 'prices.product_id');                                                 
                                    })
                                ->join('manufacturers',function ($join) {
                                            $join->on('catalog_products.manufacturer_id', '=', 'manufacturers.id')
                                                 ->where('manufacturers.active', '=', 'active');
                                    });
       
        $products = $products->select('catalog_products.*')
                             ->distinct()
                             ->where('catalog_products.active','active');
        $products = $products->orderBy('part_codes.sort')
                             ->orderBy(DB::raw('ISNULL(price), price'), 'asc')
                             ->paginate();    
        $breadcrumb='parts';
        return view('admin.partials.parts.mains.search', compact('products', 'breadcrumb', 'search', 'feeds', 'manufacturers'));
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
        return view('admin.partials.parts.mains.part', compact('breadcrumb', 'part', 'item'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(CatalogProduct $product, $slug)
    {
        //
        if (Session::has('backUrl')) Session::keep('backUrl');
        $product = $product->bySlug($slug);
        $categories = PartsCategory::select('id', 'title')->get();
        $currencies=Currency::all();        
        $breadcrumb='parts.edit';
        $item=$product;
        $productPrice = ProductPrice::where('product_id', $product->id)->first();
        $productCategories = CategoryPart::wherePart_id($product->id)->pluck('category_id')->toArray();
        $suppliers = Supplier::all(); 
        $manufacturers=Manufacturer::sorted()->get();
        return view('admin.partials.parts.form', compact('breadcrumb','item', 'currencies', 'product', 'productPrice', 'categories', 'productCategories', 'manufacturers','suppliers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CatalogProductRequest $request, CatalogProduct $product, $slug)
    {
        $product = $product->bySlug($slug);
        if (Part::update($request->all(), $product)) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if ($request->saveAndStay == 'true') return redirect()->back();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if (Session::has('backUrl')) return redirect(Session::get('backUrl'));
        if ($request->session()->has('categoryPartsUrl')) return redirect($request->session()->pull('categoryPartsUrl'));
        return redirect(route('admin-parts.index'));
    }

    /**
     * Display a listing of the resource OEM.
     *
     * @return \Illuminate\Http\Response
     */
    public function importedOEMOld()
    {
        session()->put('adminItemsUrl',url()->full());
        $products = CatalogProduct::whereType('newOEM')->paginate(session()->get('partsPerPage'));
        $breadcrumb='parts';
        return view('admin.partials.parts.mains.main', compact('products','breadcrumb'));
    }

    /**
     * Display a listing of the resource OEM.
     *
     * @return \Illuminate\Http\Response
     */
    public function importedAM()
    {
        session()->put('adminItemsUrl',url()->full());
        $products = CatalogProduct::whereType('new-am-part')->orderByDesc('id')->paginate(session()->get('partsPerPage'));
        $breadcrumb='parts';
        return view('admin.partials.parts.mains.main', compact('products','breadcrumb'));
    }

    public function importedOE()
    {
        session()->put('adminItemsUrl',url()->full());
        $products = CatalogProduct::where('type','LIKE','%OE-%')->paginate(session()->get('partsPerPage'));
        $breadcrumb = 'parts';
        return view('admin.partials.parts.mains.main', compact('products','breadcrumb'));
    }

    /**
     * Show import form
     *     
     * @return \Illuminate\Http\Response
     */
    public function excelImportForm()
    {
        //
        $breadcrumb='parts.import';
        $suppliers = Supplier::whereActive('active')->orderBy('title')->get();         
        return view('admin.partials.parts.importForm', compact('breadcrumb','suppliers'));
    }
   
    /**
     * Import products from Excel file
     *
     * @return \Illuminate\Http\Response
     */
    public function excelImport(ExcelWithSupplierImportRequest $request)
    {
        $request->file('excel')->move('public/files/import/', 'partsImport.xlsx');        
        $breadcrumb='parts.import';
        //$process = new Process(Artisan::call('importnewparts', array('email' => Auth::user()->email)));
        //$process->start();
        chdir(base_path());
        exec('bash -c "exec nohup setsid php artisan import-new-parts '.$request->supplier_id.' '.Auth::user()->email.' > /dev/null 2>&1 &"');
        flash()->success(trans('admin/common.importFlashTitle'), trans('admin/common.importFlashContent'));        
        return redirect('admin');
    }

    /**
     * Show import form
     *     
     * @return \Illuminate\Http\Response
     */
    public function excelOriginalImportForm()
    {
        //
        $breadcrumb='parts.import'; 
        $suppliers = Supplier::whereActive('active')->orderBy('title')->get();         
        return view('admin.partials.parts.originalImportForm', compact('breadcrumb','suppliers'));
    }
   

    public function excelOriginalImport(ExcelWithSupplierImportRequest $request)
    {
        $request->file('excel')->move('public/files/import/', 'OEPartsImport.xlsx');        
        $breadcrumb='parts.import';
        chdir(base_path());
        exec('bash -c "exec nohup setsid php artisan import-oe-parts '.$request->supplier_id.' > /dev/null 2>&1 &"');
        flash()->success(trans('admin/common.importFlashTitle'), trans('admin/common.importFlashContent'));        
        return redirect('admin');
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
        event(new PartDelete($product));
        if ($product->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));   
        if ($request->session()->has('categoryPartsUrl')) return redirect($request->session()->pull('categoryPartsUrl'));  
        return redirect(route('admin-parts.index'));
    }

    public function setManufacturer(Request $request)
    {
        session()->put($request->productManufacturer, $request->filter_manufacturer_id);
        session()->keep('adminPartCodeSearch');
        return redirect()->back();
    }

    public function searchParts($typeSlug, CarModelType $type, $categorySlug, PartsCategory $category, Request $request)
    {
        $type = $type->bySlug($typeSlug);         
        $category = $category->bySlug($categorySlug); 
        $manufacturers = CatalogProduct::join('type_parts', function ($join) use ($type, $category) { 
                                            $join->on('catalog_products.id', '=', 'type_parts.part_id')
                                                 ->where('type_parts.type_id', '=', $type->id);
                                    })
                                ->join('category_parts', function ($join) use ($category) { 
                                            $join->on('catalog_products.id', '=', 'category_parts.part_id')
                                                 ->where('category_parts.category_id', '=', $category->id);
                                    })
                                ->join('manufacturers', 'catalog_products.manufacturer_id', '=', 'manufacturers.id')
                            ->select('manufacturers.*')
                                ->distinct()
                                ->get();

        $productTypes = $this->categoryProductTypes($type, $category);
        $products = CatalogProduct::join('type_parts', function ($join) use ($type, $category) { 
                                            $join->on('catalog_products.id', '=', 'type_parts.part_id')
                                                 ->where('type_parts.type_id', '=', $type->id);
                                    })
                                ->join('category_parts', function ($join) use ($category) { 
                                            $join->on('catalog_products.id', '=', 'category_parts.part_id')
                                                 ->where('category_parts.category_id', '=', $category->id);
                                    })
                                ->leftJoin('catalog_price_product', function ($join) { 
                                            $join->on('catalog_products.id', '=', 'catalog_price_product.product_id');                                                 
                                    })
                                ->join('manufacturers',function ($join) {
                                            $join->on('catalog_products.manufacturer_id', '=', 'manufacturers.id')
                                                 ->where('manufacturers.active', '=', 'active');
                                    })
                                ->select('catalog_products.*', DB::raw('IF(`price` IS NOT NULL, `price`, 1000000) `price`'))
                                ->distinct();  
        $products = $products->orderBy('price')->paginate();        
        $breadcrumb = 'adminSearchCategoryParts';
        $item = $category;
        return view('admin.partials.parts.mains.searchProducts', compact('breadcrumb', 'products', 'category','type', 'manufacturers', 'item', 'productTypes'));
    }

    private function categoryProductTypes($type, $category)
    {
        $types = CatalogProduct::join('type_parts', function ($join) use ($type, $category) { 
                                            $join->on('catalog_products.id', '=', 'type_parts.part_id')
                                                 ->where('type_parts.type_id', '=', $type->id);
                                    })
                                ->join('category_parts', function ($join) use ($category) { 
                                            $join->on('catalog_products.id', '=', 'category_parts.part_id')
                                                 ->where('category_parts.category_id', '=', $category->id);
                                    })
                                ->join('manufacturers',function ($join) {
                                            $join->on('catalog_products.manufacturer_id', '=', 'manufacturers.id')
                                                 ->where('manufacturers.active', '=', 'active');
                                    })
                                ->select('catalog_products.product_group')
                                ->distinct();
        if (session()->has('categoryManufacturer')) {
            session()->keep('categoryManufacturer');
            $types->whereManufacturer_id(session()->get('categoryManufacturer'));
        }
        $types = $types->get();
        return $types;
    }

    public function partsByManufacturer($slug, Manufacturer $manufacturer)
    {

        $manufacturer = $manufacturer->bySlug($slug);
        $products = CatalogProduct::whereManufacturer_id($manufacturer->id)->paginate(session()->get('partsPerPage'));
        $breadcrumb='parts';
        return view('admin.partials.parts.mains.partsByManufacturer', compact('breadcrumb', 'products', 'manufacturer'));
    }
}
