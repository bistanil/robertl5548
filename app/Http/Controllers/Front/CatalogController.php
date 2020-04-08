<?php namespace App\Http\Controllers\Front;

use App;
use Auth;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use JavaScript;
use Carbon\Carbon;
use DB;
use URL;
use App\Http\Libraries\Meta;
use App\Models\CatalogCategory;
use App\Models\Catalog;
use App\Models\CatalogProduct;
use App\Models\CatalogAttribute;
use Illuminate\Http\Request;

class CatalogController extends Controller {

  public function __construct()
  {   
    JavaScript::put(['baseUrl' => URL::to('/')]);   
  } 

  public function categories($slug, Catalog $catalog)
    {
        $catalog = $catalog->bySlug($slug);
        $categories = CatalogCategory::sorted()->whereActive('active')->whereCatalog_id($catalog->id)->whereParent(0)->paginate();
        if ($categories->count() == 1)
        {
            $subcategories = CatalogCategory::sorted()->whereActive('active')->whereCatalog_id($catalog->id)->whereParent($categories->first()->id)->get();
            if ($subcategories->count() == 0) return redirect(route('front-products', ['catalogSlug' => $catalog->slug, 'categorySlug' => $categories->first()->slug]));
        }
        $breadcrumb = 'frontCatalogCategories';
        $item = $catalog;
        $meta = Meta::build(null, $catalog);
        return view('front.partials.products.catalogs.categories.main', compact('meta', 'breadcrumb', 'categories', 'item', 'catalog'));
    }

  public function subcategories($catalogSlug, Catalog $catalog, $categorySlug, CatalogCategory $category)
  { 
    $catalog = $catalog->bySlug($catalogSlug);
    $category = $category->bySlug($categorySlug);
    $categories = CatalogCategory::whereActive('active')->whereParent($category->id)->get();
    if ($categories->count() < 1) return redirect(route('front-products', ['catalogSlug' => $catalogSlug, 'categorySlug' => $categorySlug]));
    $breadcrumb = 'frontCatalogCategories';
    $meta = Meta::build(null, $category);
    $item = $category;
    return view('front.partials.products.catalogs.categories.subcategories', compact('meta', 'breadcrumb', 'categories', 'category', 'item', 'catalog'));
  }

  public function products($catalogSlug, Catalog $catalog, $categorySlug, CatalogCategory $category, Request $request)
    {
        if (url()->current() != session()->get('productsUrl')) {
            session()->forget('categoryManufacturer');
            session()->forget('selectedAttributes');
        }
       // session()->forget('categoryManufacturer');
        session()->put('productsUrl', url()->current());
        $catalog = Catalog::whereSlug($catalogSlug)->get()->first();
        $attributes = $catalog->attributes()->whereActive('active')->orderBy('position')->get();
        if ($request->has('_token')) $this->setFilters($request, $attributes);                
        $category = CatalogCategory::whereSlug($categorySlug)->get()->first();
        //DB::enableQueryLog();
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
                                            //$join->where('catalog_price_product.source', '=', 'admin');
                                    })
                             //->distinct()                             
                             ->orderBy(DB::raw('IF(`price` IS NOT NULL, `price`, 1000000)'))
                             ->groupBy('catalog_products.id', 'price')
                             ->orderBy('price', 'asc')
                             ->paginate(16, 'catalog_products.*'); 
        //dd(DB::getQueryLog());
        $activeAttributes = $this->getActiveAttributes($catalog, $attributes, $category);        
        $activeAttributesLists = $this->getActiveAttributesLists($catalog, $attributes, $category, $activeAttributes);
        $manufacturers = $this->getManufacturers($catalog, $category);
        $item = $category;        
        $breadcrumb = 'frontCatalogProducts';
        $meta = Meta::build(null, $category);
        return view('front.partials.products.catalogs.products.main', compact('meta', 'breadcrumb', 'products', 'attributes', 'activeAttributes', 'activeAttributesLists', 'manufacturers', 'category', 'item'));
    }

  public function product($slug, CatalogProduct $product)
    {
        session()->keep('productsUrl');
        $product = $product->bySlug($slug);
        $item = $product;
        $breadcrumb = 'frontCatalogProduct';
        $meta = Meta::build(null, $product);
        return view('front.partials.products.catalogs.products.show', compact('meta', 'breadcrumb', 'product', 'item'));   
    }

  public function search(Request $request)
    { 
      if (isset($request->search)) $request->session()->flash('productSearch',$request->search);
      $request->session()->keep('productSearch');         
      $search = $request->session()->get('productSearch');
      $products=CatalogProduct::Join('manufacturers', function($join) use($search){
                                    $join->on('catalog_products.manufacturer_id','=','manufacturers.id' );
                                })
                                ->select('catalog_products.*') 
                                ->where('catalog_products.catalog_id','!=',0)
                               
                                ->where(function ($query) use ($search){
                                $query->orWhere('catalog_products.title', 'LIKE', "%$search%")
                                      ->orWhere('catalog_products.code', 'LIKE', "%$search%")
                                      ->orWhere('catalog_products.content', 'LIKE', "%$search%")
                                      ->orWhere('manufacturers.title', 'LIKE', "%$search%");

                                })                                                  
                                ->paginate(16);  
      $searchTerm=$request->search;                                                 
      if($products != null){ 
        $item=$products;
        $breadcrumb='frontCatalogSearch';
        $meta = Meta::build('Search');
        return view('front.partials.products.catalogs.products.search',compact('meta','breadcrumb','products','item','searchTerm'));
        }
      else{
        $breadcrumb='noProduct';
        $meta = Meta::build('Search');
        return view('front.partials.products.catalogs.products.noProduct' ,compact('meta','breadcrumb','product','item'));
      }
    }

  public function seeAll()
  {
    $catalogs=Catalog::whereActive('active')->get();
    $breadcrumb = 'frontCatalogs';
    $meta = Meta::build(null,$catalogs);
    return view ('front.partials.products.catalogs.main',compact('meta','catalogs','breadcrumb'));
  }

  public function specialOffers(CatalogProduct $products)
  {
    $products = $products->select('catalog_products.*')
                             ->where('active','active')
                             ->where('offer','yes')
                             ->paginate();
    $breadcrumb = 'frontSpecialOffers';
    $meta = Meta::build('SpecialOffers');
    return view ('front.partials.products.specialOffers',compact('meta','products','breadcrumb'));
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

}