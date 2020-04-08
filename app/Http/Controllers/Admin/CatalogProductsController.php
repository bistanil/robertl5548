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
use App\Models\Catalog;
use App\Models\CatalogAttribute;
use App\Models\Manufacturer;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\ProductAttribute;
use App\Models\ProductDimension;
use App\Models\Currency;
use App\Models\ProductCategory;
use App\Models\CatalogCategory;
use App\Models\Supplier;
use App\Http\Requests\Admin\CatalogProductRequest;
use App\Http\Requests\Admin\ExcelImportRequest;
use App\Events\ProductDelete;
use Excel;
use URL;
use JavaScript;
use Artisan;
use Product;
use ProdPrice;

class CatalogProductsController extends Controller
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
    public function index($slug, Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        $parent = Catalog::whereSlug($slug)->get()->first();
        $products = $parent->products()->orderBy('code')->paginate(session()->get('catalogPerPage'));
        $breadcrumb = 'catalogProducts';
        $item = $parent;                
        $request->session()->forget('categoryProductsUrl');
        return view('admin.partials.catalogs.products.mains.main', compact('products','breadcrumb','item','parent'));
    }

    public function products($catalogSlug, $categorySlug, Request $request)
    {
        if (url()->current() != session()->get('productsUrl')) {
            session()->forget('categoryManufacturer');
            session()->forget('selectedAttributes');
        }
        session()->put('productsUrl', url()->current());
        $attributes = $catalog->attributes($catalogSlug)->whereActive('active')->orderBy('position')->get();
        if ($request->has('_token')) $this->setFilters($request, $attributes);                
        $category = $category->bySlug($categorySlug);
        $catalog = $catalog->bySlug($catalogSlug);
        $products = CatalogProduct::join('catalog_category_product', function ($join) use ($category) { 
                                            $join->on('catalog_products.id', '=', 'catalog_category_product.product_id')
                                                 ->where('catalog_category_product.category_id', '=', $category->id);
                                    });
        foreach ($attributes as $key => $attribute) {
          if (session()->has('selectedAttributes.filterAttribute'.$attribute->id)) {
              if (count(session()->get('selectedAttributes.filterAttribute'.$attribute->id)) > 0)    
              $products->join('catalog_attribute_product as t'.$key, function ($join) use ($catalog, $key, $attribute) { 
                                            $join->on('catalog_products.id', '=', 't'.$key.'.product_id')
                                                 ->where('catalog_products.catalog_id', '=', $catalog->id);
                                            if (session()->has('selectedAttributes.filterAttribute'.$attribute->id)) {
                                                $join->whereIn('t'.$key.'.value', session()->get('selectedAttributes.filterAttribute'.$attribute->id));
                                            }
                                    });
          }
        }
        if (count(session()->get('categoryManufacturer')) > 0) $products->whereIn('manufacturer_id', session()->get('categoryManufacturer'));
        $products->whereActive('active')
                 ->whereLanguage(App::getLocale());            
        $products = $products->select('catalog_products.*', 'price')
                             ->leftJoin('catalog_price_product', function ($join) { 
                                            $join->on('catalog_products.id', '=', 'catalog_price_product.product_id');                                                 
                                            $join->where('catalog_price_product.source', '=', 'admin');
                                    })
                             ->distinct()                             
                             ->orderBy(DB::raw('IF(`price` IS NOT NULL, `price`, 1000000)'))
                             ->orderBy('price', 'asc')
                             ->paginate(12);    
        $activeAttributes = $this->getActiveAttributes($catalog, $attributes, $category);        
        $activeAttributesLists = $this->getActiveAttributesLists($catalog, $attributes, $category, $activeAttributes);
        $manufacturers = $this->getManufacturers($catalog, $category);
        $item = $category;        
        $breadcrumb = 'catalogProducts';
        $meta = Meta::build(null, $category);
        return view('admin.partials.catalogs.products.mains.main', compact('meta', 'breadcrumb', 'products', 'attributes', 'activeAttributes', 'activeAttributesLists', 'manufacturers', 'category', 'item'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {   
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('productSearch',$request->q);
        $request->session()->keep('productSearch');         
        $search = $request->session()->get('productSearch');
        $products = CatalogProduct::Join('manufacturers', function($join) use($search){
                                        $join->on('catalog_products.manufacturer_id','=','manufacturers.id' );
                                     })
                                    ->select('catalog_products.*')
                                    ->where('catalog_products.catalog_id', '!=','0' )
                                    ->where(function ($query) use ($search){
                                        $query->orWhere('catalog_products.title', 'LIKE', "%$search%")
                                              ->orWhere('catalog_products.code', 'LIKE', "%$search%")
                                              ->orWhere('catalog_products.content', 'LIKE', "%$search%")
                                              ->orWhere('manufacturers.title', 'LIKE', "%$search%");
                                    })                                                    
                                    ->paginate(session()->get('catalogPerPage'));
        $breadcrumb = 'catalogProductsSearch';      
        return view('admin.partials.catalogs.products.mains.search', compact('products', 'breadcrumb', 'search'));
    }

    public function searchCatalogProducts(Request $request)
    {   
        session()->put('adminItemsUrl', url()->current());
        if (isset($request->q)) {
            session()->put('productSearch',$request->q);
        }    
        $search = session()->get('productSearch');

        //session()->put('adminItemsUrl',url()->full());
        //if (isset($request->q)) $request->session()->flash('productSearch',$request->q);
        //$request->session()->keep('productSearch');         
        //$search = $request->session()->get('productSearch');
        $products = CatalogProduct::Join('manufacturers', function($join) use($search){
                                        $join->on('catalog_products.manufacturer_id','=','manufacturers.id' );
                                     })
                                    ->select('catalog_products.*')
                                    ->where('catalog_products.catalog_id', '!=','0' )
                                    ->where(function ($query) use ($search){
                                        $query->orWhere('catalog_products.title', 'LIKE', "%$search%")
                                              ->orWhere('catalog_products.code', 'LIKE', "%$search%")
                                              ->orWhere('catalog_products.content', 'LIKE', "%$search%")
                                              ->orWhere('manufacturers.title', 'LIKE', "%$search%");
                                    })                                                    
                                    ->paginate(session()->get('catalogPerPage'));
        $breadcrumb='catalogProductsSearch';      
        return view('admin.partials.catalogs.products.mains.productsSearch', compact('products', 'breadcrumb', 'search'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function category($slug, Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        $parent =  CatalogCategory::whereSlug($slug)->get()->first();
        $products = $parent->products()->paginate();
        $breadcrumb = 'catalogProducts';
        $item = $parent; 
        $request->session()->flash('categoryProductsUrl',$request->fullUrl());
        $request->session()->keep('categoryProductsUrl');        
        return view('admin.partials.catalogs.products.mains.category', compact('products','breadcrumb','item','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug)
    {
        $parent = Catalog::whereSlug($slug)->get()->first();
        $categories = $parent->categories()->get();
        $attributes = $parent->attributes()->where('active', '=', 'active')->get();
        $manufacturers = Manufacturer::sorted()->get();
        $currencies = Currency::all();
        $suppliers = Supplier::all();
        $breadcrumb = 'catalogProducts.create';
        $item = $parent;        
        return view('admin.partials.catalogs.products.form', compact('breadcrumb','parent','item', 'categories', 'manufacturers', 'attributes', 'currencies','suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CatalogProductRequest $request, $slug)
    {
        $catalog = Catalog::whereSlug($slug)->get()->first();        
        $info = $request->all();
        $info['catalog_id'] = $catalog->id;
        if (Product::store($info)) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText')); 
        if ($request->saveAndStay == 'true') return redirect()->back();     
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if ($request->session()->has('categoryProductsUrl')) return redirect($request->session()->pull('categoryProductsUrl'));
        return redirect(route('admin-catalog-products.index', ['catalogSlug' => $catalog->slug] ));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug, Request $request, CatalogProduct $product)
    {
        $request->session()->flash('categoryProductsUrl',$request->fullUrl());
        $request->session()->keep('categoryProductsUrl');
        $product = $product->bySlug($slug);
        $breadcrumb = 'catalogProducts.details';
        $item = $product;
        return view('admin.partials.catalogs.products.show', compact('breadcrumb', 'product', 'item'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Catalog $catalog, $slug, $productSlug)
    {
        //
        $product = CatalogProduct::whereSlug($productSlug)->get()->first();
        $parent = Catalog::whereSlug($slug)->get()->first();
        $categories = $parent->categories()->get();
        $attributes = $parent->attributes()->where('active', '=', 'active')->get();
        $manufacturers = Manufacturer::sorted()->get();
        $currencies = Currency::all();
        $suppliers = Supplier::all(); 
        $breadcrumb = 'catalogProducts.edit';
        $item = $product;
        //$productPrice = ProductPrice::where('product_id', $product->id)->first();
        $productDimensions = ProductDimension::where('product_id', $product->id)->first();
        $productAttributes = ProductAttribute::where('product_id', $product->id)->get();
        $productCategories = ProductCategory::where('product_id', $product->id)->pluck('category_id')->toArray();
        return view('admin.partials.catalogs.products.form', compact('breadcrumb','parent','item', 'categories', 'manufacturers', 'attributes', 'currencies', 'product', 'productPrice', 'productAttributes', 'productCategories', 'productDimensions','suppliers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CatalogProductRequest $request, $slug, $productSlug)
    {
        // 

        $product = CatalogProduct::whereSlug($productSlug)->get()->first();
        $catalog = Catalog::whereSlug($slug)->get()->first();        
        if (Product::update($request->all(), $product)) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText')); 
        if ($request->saveAndStay == 'true') return redirect()->back();       
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if ($request->session()->has('categoryProductsUrl')) return redirect($request->session()->pull('categoryProductsUrl'));
        return redirect(route('admin-catalog-products.index', ['catalogSlug' => $catalog->slug] ));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function categorySortUp($slug, $parentId, $entityId)
    {
        //
        $category = CatalogCategory::whereSlug($slug)->get()->first();        
        $entity = ProductCategory::where('category_id', $category->id)->where('product_id', $entityId)->first(); 
        $positionEntity = ProductCategory::where('category_id', $category->id)->where('product_id', $parentId)->first();
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-category-products', ['slug' => $slug] ));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function categorySortDown($slug, $parentId,$entityId)
    {
        //
        $category = CatalogCategory::whereSlug($slug)->get()->first();
        $entity = ProductCategory::where('category_id', $category->id)->where('product_id', $entityId)->first();        
        $positionEntity = ProductCategory::where('category_id', $category->id)->where('product_id', $parentId)->first();
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-catalog-category-products', ['slug' => $slug] ));
    }

    /**
     * Export products to excel
     *
     * @param  int  $slug     
     */
    public function excelExport($slug)
    {
        $catalog = Catalog::whereSlug($slug)->get()->first();
        $excel = App::make('excel');
        //return view('admin.partials.catalogs.products.excel', compact('catalog'));
        Excel::create(trans('admin/catalogs.products').'-'.$catalog->title, function($excel) use ($catalog) {
            $excel->sheet(trans('admin/catalogs.products'), function($sheet) use ($catalog) {
                $sheet->loadView('admin.partials.catalogs.products.excel')
                      ->with('catalog', $catalog);
            })->download('xlsx');
        });        
    }
    /**
     * Display Excel import form.
     *
     * @return \Illuminate\Http\Response
     */
    public function excelImportForm($slug)
    {
        $item = Catalog::whereSlug($slug)->get()->first();
        $breadcrumb = 'catalogProducts.import';         
        return view('admin.partials.catalogs.products.importForm', compact('breadcrumb','item'));
    }

    /**
     * Import products from Excel file
     *
     * @return \Illuminate\Http\Response
     */
    public function excelImport($slug, ExcelImportRequest $request)
    {
        $request->file('excel')->move('public/files/import/', 'productsImport.xlsx');
        $catalog = Catalog::whereSlug($slug)->get()->first();
        //Artisan::call("importcatalogexcel '.$catalog->id'");
        chdir(base_path());
        exec('bash -c "exec nohup setsid php artisan import-catalog-excel '.$catalog->id.' > /dev/null 2>&1 &"');
        if ($catalog->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-catalogs');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $catalogSlug, $productSlug, Request $request)
    {
        //
        $product = CatalogProduct::whereSlug($productSlug)->get()->first();        
        event(new ProductDelete($product));
        $parent = $product->where('id', $product->parent)->first();
        if ($product->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));   
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        if ($request->session()->has('categoryProductsUrl')) return redirect($request->session()->pull('categoryProductsUrl'));  
        return redirect(route('admin-catalog-products.index', ['catalogSlug' => $catalogSlug] ));
    }

    public function setPerPage(Request $request)
    {
        session()->put('catalogPerPage', $request->per_page);
        return redirect()->back();
    }

    public function getActiveAttributes($catalog, $attributes, $category)
    {
        $activeAttributes = CatalogAttribute::join('catalog_attribute_product', function  ($join) use ($catalog) {
                                                    $join->on('catalog_attributes.id', '=', 'catalog_attribute_product.attribute_id');                                                         
                                            })
                                            ->join('catalog_products', function ($join) use ($catalog) {
                                                    $join->on('catalog_attribute_product.product_id', '=', 'catalog_products.id')
                                                         ->where('catalog_products.catalog_id', '=', $catalog->id);
                                            })     
                                            ->join('catalog_category_product', function ($join) use ($category) {
                                                    $join->on('catalog_products.id', '=', 'catalog_category_product.product_id')
                                                         ->where('catalog_category_product.category_id', '=', $category->id);
                                            });        
        foreach ($attributes as $key => $attribute) {
          if (session()->has('selectedAttributes.filterAttribute'.$attribute->id)) {              
              if (count(session()->get('selectedAttributes.filterAttribute'.$attribute->id)) > 0)    
              $activeAttributes->join('catalog_attribute_product as t'.$key, function ($join) use ($catalog, $key, $attribute) { 
                                            $join->on('catalog_products.id', '=', 't'.$key.'.product_id')
                                                 ->where('catalog_products.catalog_id', '=', $catalog->id);
                                            if (session()->has('selectedAttributes.filterAttribute'.$attribute->id)) {
                                                $join->whereIn('t'.$key.'.value', session()->get('selectedAttributes.filterAttribute'.$attribute->id));
                                            }
                                    });
          }
        }
        $activeAttributes->where('catalog_products.active', '=', 'active')
                         ->where('catalog_attributes.active', '=', 'active')
                         ->where('catalog_attributes.is_filter', '=', 'yes')
                         ->where('catalog_products.language', '=', App::getLocale())
                         ->where('catalog_attributes.language', '=', App::getLocale());
        if (count(session()->get('categoryManufacturer')) > 0) $activeAttributes->whereIn('manufacturer_id', session()->get('categoryManufacturer'));       
        $activeAttributes = $activeAttributes->select('catalog_attributes.*')
                             ->distinct()
                             ->orderBy('position')
                             ->get();
        return $activeAttributes;
    }

  public function getActiveAttributesLists($catalog, $attributes, $category, $activeAttributes)
    {        
        $lists = [];
        foreach ($activeAttributes as $key => $activeAttribute) {          
          if ($activeAttribute->id != session()->get('firstProductFilter'))
          { 
            $activeAttributesValues = CatalogProduct::join('catalog_category_product', function ($join) use ($category) { 
                                                $join->on('catalog_products.id', '=', 'catalog_category_product.product_id')
                                                     ->where('catalog_category_product.category_id', '=', $category->id);
                                        });
            $activeAttributesValues->join('catalog_attribute_product', function ($join) use ($catalog) { 
                                                $join->on('catalog_products.id', '=', 'catalog_attribute_product.product_id')
                                                     ->where('catalog_products.catalog_id', '=', $catalog->id);                                            
                                        });
            $activeAttributesValues->join('catalog_attributes', function ($join) use ($catalog) { 
                                                $join->on('catalog_attributes.id', '=', 'catalog_attribute_product.attribute_id')
                                                     ->where('catalog_products.catalog_id', '=', $catalog->id);                                            
                                        });
            if ($activeAttribute->list_id > 0) $activeAttributesValues->join('catalog_list_items', function ($join) { 
                                                $join->on('catalog_attribute_product.value', '=', 'catalog_list_items.value');
                                        });
            foreach ($attributes as $key => $attribute) {
              if (session()->has('selectedAttributes.filterAttribute'.$attribute->id)) {
                  if (count(session()->get('selectedAttributes.filterAttribute'.$attribute->id)) > 0)    
                  $activeAttributesValues->join('catalog_attribute_product as t'.$key, function ($join) use ($catalog, $key, $attribute) { 
                                                $join->on('catalog_products.id', '=', 't'.$key.'.product_id')
                                                     ->where('catalog_products.catalog_id', '=', $catalog->id);
                                                if (session()->has('selectedAttributes.filterAttribute'.$attribute->id)) {
                                                    $join->whereIn('t'.$key.'.value', session()->get('selectedAttributes.filterAttribute'.$attribute->id));
                                                }
                                        });
              }
            }
            $activeAttributesValues->where('catalog_products.active', '=', 'active')
                             ->where('catalog_attributes.active', '=', 'active')
                             ->where('catalog_products.language', '=', App::getLocale())
                             ->where('catalog_attributes.language', '=', App::getLocale())
                             ->where('catalog_attribute_product.attribute_id', '=', $activeAttribute->id);
            if (count(session()->get('categoryManufacturer')) > 0) $activeAttributesValues->whereIn('manufacturer_id', session()->get('categoryManufacturer'));
            $activeAttributesValues->select('catalog_attribute_product.value');
            $activeAttributesValues->distinct();
            if ($activeAttribute->list_id > 0) $activeAttributesValues->orderBy('catalog_list_items.position');
            $lists[$activeAttribute->id] = $this->processListValue($activeAttributesValues->get());
          } else {

            $activeAttributesValues = CatalogProduct::join('catalog_category_product', function ($join) use ($category) { 
                                                $join->on('catalog_products.id', '=', 'catalog_category_product.product_id')
                                                     ->where('catalog_category_product.category_id', '=', $category->id);
                                        });
            $activeAttributesValues->join('catalog_attribute_product', function ($join) use ($catalog) { 
                                                $join->on('catalog_products.id', '=', 'catalog_attribute_product.product_id')
                                                     ->where('catalog_products.catalog_id', '=', $catalog->id);                                            
                                        });
            $activeAttributesValues->join('catalog_attributes', function ($join) use ($catalog) { 
                                                $join->on('catalog_attributes.id', '=', 'catalog_attribute_product.attribute_id')
                                                     ->where('catalog_products.catalog_id', '=', $catalog->id);                                            
                                        });
            if ($activeAttribute->list_id > 0) $activeAttributesValues->join('catalog_list_items', function ($join) { 
                                                $join->on('catalog_attribute_product.value', '=', 'catalog_list_items.value');                                                     
                                        });
            $activeAttributesValues->where('catalog_products.active', '=', 'active')
                             ->where('catalog_attributes.active', '=', 'active')
                             ->where('catalog_products.language', '=', App::getLocale())
                             ->where('catalog_attributes.language', '=', App::getLocale())
                             ->where('catalog_attribute_product.attribute_id', '=', $activeAttribute->id);
            if (count(session()->get('categoryManufacturer')) > 0) $activeAttributesValues->whereIn('manufacturer_id', session()->get('categoryManufacturer'));        
            $activeAttributesValues->select('catalog_attribute_product.value', 'catalog_list_items.position');
            $activeAttributesValues->distinct();
            if ($activeAttribute->list_id > 0) $activeAttributesValues->orderBy('catalog_list_items.position');
            //if ($activeAttribute->id == 29) dd($activeAttributesValues->get());            
            $lists[$activeAttribute->id] = $this->processListValue($activeAttributesValues->get());            
          }
        }
        
        return $lists;
    }

  public function resetFilters($catalogSlug)
    {
       session()->forget('selectedAttributes');    
       session()->forget('firstProductFilter');
       session()->forget('categoryManufacturer');
       return redirect(session()->get('productsUrl'));    
    }

  public function removeFilter(Request $request)
    {        
        $attributeFilters = collect(session()->get('selectedAttributes.filterAttribute'.$request->attribute));        
        if ($attributeFilters->search($request->value) != null) $attributeFilters->pull($attributeFilters->search($request->value));        
        session()->put('selectedAttributes.filterAttribute'.$request->attribute, $attributeFilters->toArray());        
        return redirect()->back();
    }

  private function processListIdValue($list)
    {
        $processedList = ['' => trans('admin/common.selectItem')];
        foreach ($list as $item) {
          $processedList[$item->id] = $item->value;
        }
        return $processedList;
    }

  private function processListValue($list)
    {     
        $processedList = ['' => trans('admin/common.selectItem')];
        foreach ($list as $item) {
          if ($item->value != null) $processedList[$item->value] = $item->value;
        }        
        return $processedList;
    }

  private function checkFirstFilter($attributeId)
    {
        if (session()->has('selectedAttributes') == FALSE) session()->put('firstProductFilter', $attributeId);
        else if (count(session()->get('selectedAttributes')) == 0) session()->put('firstProductFilter', $attributeId);
    }

  public function setFilters($request, $attributes)
    {
      
        if ($request->has('filter_manufacturer_id') ) {
              if (session()->has('categoryManufacturer')) $valuesArr = session()->get('categoryManufacturer');
              else $valuesArr = [];                                        
              if (!in_array($request->filter_manufacturer_id, $valuesArr) && $request->filter_manufacturer_id != null) $valuesArr[count($valuesArr)+1] = $request->filter_manufacturer_id;
              session()->put('categoryManufacturer', $valuesArr);
            }
            foreach ($attributes as $key => $attribute) {
                $attributeVar = 'attribute_'.$attribute->id;
                if ($request->has($attributeVar)) {
                    if (session()->has('selectedAttributes.filterAttribute'.$attribute->id)) $valuesArr = session()->get('selectedAttributes.filterAttribute'.$attribute->id);
                    else {
                      $this->checkFirstFilter($attribute->id);
                      $valuesArr = [];                    
                    }
                    if (!in_array($request->$attributeVar, $valuesArr) && $request->$attributeVar != '') $valuesArr[count($valuesArr)+1] = $request->$attributeVar;
                    session()->put('selectedAttributes.filterAttribute'.$attribute->id, $valuesArr);
                }                
            }
    }

  public function getManufacturers($catalog, $category)
    {
      $manufacturers = CatalogProduct::join('catalog_category_product', function ($join) use ($category) { 
                                                $join->on('catalog_products.id', '=', 'catalog_category_product.product_id')
                                                     ->where('catalog_category_product.category_id', '=', $category->id);
                                        })
                                       ->join('manufacturers', function ($join) { 
                                                $join->on('catalog_products.manufacturer_id', '=', 'manufacturers.id');                                                     
                                        })
                                       ->where('manufacturers.active', '=', 'active')
                                       ->where('catalog_products.language', '=', App::getLocale())
                                       ->select('manufacturers.*')
                                       ->distinct()
                                       ->orderBy('position')
                                       ->get();
      return $manufacturers;
    }

  public function removeManufacturerFilter($manufacturerId) {      
      $manufacturers = session()->get('categoryManufacturer');
      foreach ($manufacturers as $key => $manufacturer) {
         if ($manufacturer == $manufacturerId) unset($manufacturers[$key]);
      }
      session()->put('categoryManufacturer', $manufacturers);
      return redirect()->back();
    }

    public function productsByManufacturer($manufacturerSlug)
    {
    session()->put('adminItemsUrl',url()->full());
    $manufacturer = Manufacturer::whereSlug($manufacturerSlug)->get()->first();
    $products = CatalogProduct::where('active','active')
                              ->where('manufacturer_id', $manufacturer->id)
                              ->paginate(session()->get('productsPerPage'));
    $breadcrumb = 'catalogProductsSearch';
    return view ('admin.partials.catalogs.products.productsByManufacturer',compact('meta','products','breadcrumb', 'manufacturer'));
    }
}
