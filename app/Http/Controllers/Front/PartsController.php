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
use App\Http\Libraries\Elitws;
use App\Models\PartsCategory;
use App\Models\CarModelType;
use App\Models\CatalogProduct;
use App\Models\Car;
use App\Models\Manufacturer;
use App\Models\PartCode;
use App\Models\TypePart;
use Illuminate\Http\Request;
use App\Http\Libraries\WSPrices;
use App\Models\TransportMargin;
use Cart;

class PartsController extends Controller {

    public function __construct()
    {       
        JavaScript::put(['baseUrl' => URL::to('/')]);       
    }   

    public function index($typeSlug, CarModelType $type, $categorySlug, Request $request)
    {   
        session()->put('productsUrl', url()->current());
        $type = $type->bySlug($typeSlug);
        $category = PartsCategory::whereSlug($categorySlug)->get()->first();
        session()->put('partsCategory', $category);       
        if ($request->has('_token'))
        {
            if ($request->has('filter_manufacturer_id') ) session()->flash('categoryManufacturer', $request->filter_manufacturer_id);
            else session()->forget('categoryManufacturer');
            if ($request->has('product_type') ) session()->put('categoryProductType', $request->product_type);
            else session()->forget('categoryProductType');                
        } 
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
                                ->select('catalog_products.*')
                                ->distinct();
        if (session()->has('categoryManufacturer')) {
            session()->keep('categoryManufacturer');
            if (session()->get('categoryManufacturer') != '') $products->whereManufacturer_id(session()->get('categoryManufacturer'));
        }
        if (session()->has('categoryProductType')) {
            session()->keep('categoryProductType');
            if (session()->get('categoryProductType') != '') $products->whereProduct_group(session()->get('categoryProductType'));
        }
        $products = $products->orderBy(DB::raw('IF(`price` IS NOT NULL, `price`, 1000000)'))
                             ->groupBy('catalog_products.id', 'price')
                             ->orderBy('price', 'asc')
                             ->paginate(16);
        foreach ($products as $key => $product) {
            $ws = new Elitws();
            $ws->setProduct($product);
            $ws->process();
        }
        $breadcrumb = 'frontCategoryParts';
        $meta = Meta::build(null, $category);
        $item = $category;
        return view('front.partials.products.parts.products.main', compact('meta','breadcrumb', 'products', 'category','type', 'manufacturers', 'item', 'productTypes'));
    }

    public function subcategories($typeSlug, $categorySlug, CarModelType $type, PartsCategory $category)
    {   
        $type = $type->bySlug($typeSlug);
        $category = $category->bySlug($categorySlug);
        session()->put('partsCategory', $category);
        $categories = collect(DB::select(DB::raw("SELECT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent=".$category->id." AND type_categories.type_id=".$type->id." AND parts_categories.active = 'active' ORDER BY position")));
        $breadcrumb = 'frontPartsSubcategories';
        $meta = Meta::build(null, $category);
        $item = $category;
        return view('front.partials.products.parts.products.main', compact('meta', 'breadcrumb', 'categories', 'type', 'category', 'item'));
    }

    public function setRequestOfferPart($slug, CatalogProduct $product)
    {
        $product = $product->bySlug($slug);
        session()->put('part', $product);
        return redirect(route('front-request-offer'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {   
        session()->put('productsUrl', url()->current());
        session()->forget('frontPart');
        if (isset($request->search)) {
            session()->forget('manufacturerOrder');
            session()->forget('productGroup');
            session()->put('partCodeSearch',$request->search);
        } 


        if ($request->has('_token'))
        {
            if ($request->has('filter_manufacturer_id')) session()->flash('manufacturerOrder', $request->filter_manufacturer_id);
            else session()->forget('manufacturerOrder'); 
            if ($request->has('group')) session()->flash('productGroup', $request->group);
            else session()->forget('productGroup');                               
        }       

        //session()->keep('partCodeSearch');
        $search = session()->get('partCodeSearch');
        $products = CatalogProduct::join('part_codes', function ($join) use ($search) { 
                                            $join->on('catalog_products.id', '=', 'part_codes.part_id')
                                                 ->where('part_codes.code', 'LIKE', preg_replace("/[^a-zA-Z0-9]+/","",$search));
                                    })
                                ->leftJoin('catalog_price_product', function ($join) { 
                                            $join->on('catalog_products.id', '=', 'catalog_price_product.product_id')
                                                 ->whereRaw('catalog_price_product.id = (select id from catalog_price_product where product_id = catalog_products.id order by price limit 1 )');
                                    })
                                ->join('manufacturers',function ($join) {
                                            $join->on('catalog_products.manufacturer_id', '=', 'manufacturers.id')
                                                 ->where('manufacturers.active', '=', 'active');
                                    });
       
        $products = $products->select('catalog_products.*')
                             ->distinct()
                             ->where('catalog_products.active','active');
        if(session()->get('manufacturerOrder') != '') {
            $products = $products->where('manufacturers.id', '=', session()->get('manufacturerOrder'));
        }
        if (session()->get('productGroup') != '') {
            $products->where('catalog_products.product_group', session()->get('productGroup'));
        }
        $products = $products->orderBy('part_codes.sort')
                             ->orderBy(DB::raw('ISNULL(price), price'), 'asc')
                             ->paginate(16);
        $manufacturers = Manufacturer::join('catalog_products', function ($join) {
                                            $join->on('manufacturers.id', '=', 'catalog_products.manufacturer_id');
                                        })
                                    ->join('part_codes', function ($join) use ($search) { 
                                            $join->on('catalog_products.id', '=', 'part_codes.part_id')
                                                 ->where('part_codes.code', 'LIKE', preg_replace("/[^a-zA-Z0-9]+/","",$search));
                                        })
                                    ->where('manufacturers.active', '=', 'active')
                                    ->select('manufacturers.*')
                                    ->distinct()
                                    ->get();
        $ws = new WSPrices($products);
        //$ws->autonet();
        $total = str_replace(',', '', Cart::instance('shopping')->subtotal());
        $transport = TransportMargin::where('margin','!=',0)->get()->first()->max;        
        $item = null;
        foreach ($products as $key => $product) {
            $ws = new Elitws();
            $ws->setProduct($product);
            $ws->process();
        }
        if (session()->has('type')) {
            $breadcrumb = 'frontPartSearchWithType';
            $item = session()->get('type');
        }
        else $breadcrumb = 'frontPartSearch';
        $meta = Meta::build('Search');
        $groups = $this->getGroups($search,null,null);
        return view('front.partials.products.parts.products.search', compact('meta','breadcrumb', 'products', 'transport', 'total', 'manufacturers', 'groups', 'item'));
    }

    public function show($slug, CatalogProduct $product)
    {
        session()->keep('productsUrl');
        $product = $product->bySlug($slug);
        $types = $product->types()->whereActive('active')->orderBy('id')->paginate(); 
        if ($product->partsCategories->sortBy('category_id')->last() != null) $categoryId = $product->partsCategories->sortBy('category_id')->last()->id;
        else $categoryId = 0;       
        $equivalences = PartCode::join('category_parts', function ($join) use ($product) {
                                        if ($product->partsCategories->sortBy('category_id')->last() != null) $id = $product->partsCategories->sortBy('category_id')->last()->id;
                                        else $id = 0;
                                        $join->on('part_codes.part_id', '=', 'category_parts.part_id')
                                             ->where('category_parts.category_id', '=', $id);
                                  })
                                ->whereCode(preg_replace("/[^a-zA-Z0-9]+/","",$product->code))
                                ->paginate();
        if (session()->has('partsCategory')) $breadcrumb = 'frontCategoryPart';
        else $breadcrumb = 'frontSearchPart';
        $meta = Meta::build(null, $product);
        $item = $product;
        return view('front.partials.products.parts.products.show', compact('meta', 'breadcrumb', 'types', 'equivalences', 'product', 'item'));
    }

    public function showWithType($typeSlug, $slug, CatalogProduct $product)
    {
        session()->keep('productsUrl');
        $product = $product->bySlug($slug);
        $types = $product->types()->whereActive('active')->orderBy('id')->paginate();        
        $equivalences = PartCode::join('category_parts', function ($join) use ($product) {
                                        $join->on('part_codes.part_id', '=', 'category_parts.part_id')
                                             ->where('category_parts.category_id', '=', $product->partsCategories->sortBy('category_id')->last()->id);
                                  })
                                ->whereCode(preg_replace("/[^a-zA-Z0-9]+/","",$product->code))
                                ->limit(15)
                                ->get();
        if (session()->has('partsCategory')) $breadcrumb = 'frontCategoryPart';
        else $breadcrumb = 'frontSearchPart';
        $meta = Meta::build(null, $product);
        $item = $product;
        return view('front.partials.products.parts.products.show', compact('meta', 'breadcrumb', 'types', 'equivalences', 'product', 'item'));
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
            if (session()->get('categoryManufacturer') != '') $types->whereManufacturer_id(session()->get('categoryManufacturer'));
        }        
        $types = $types->get();
        return $types;
    }

    public function getGroups($type,$category = null,$location = null)
    {
        if($location == 'main') {
            $products = TypePart::join('category_parts', function ($join) use ($category, $type) { 
                                            $join->where('type_parts.type_id', '=', $type->id)
                                                 ->where('category_parts.category_id', '=', $category->id)
                                                 ->on('type_parts.part_id', '=', 'category_parts.part_id');
                                    })
                                ->join('catalog_products',function ($join) {
                                            $join->on('catalog_products.id', '=', 'type_parts.part_id')
                                                 ->where('catalog_products.active', '=', 'active');
                                    })
                                ->join('manufacturers',function ($join) {
                                            $join->on('catalog_products.manufacturer_id', '=', 'manufacturers.id')
                                                 ->where('manufacturers.active', '=', 'active');
                                    })
                                ->leftJoin('catalog_price_product', function ($join) { 
                                            $join->on('catalog_products.id', '=', 'catalog_price_product.product_id');                                                 
                                    });                      
        $products = $products->select('type_parts.part_id')
                             ->distinct();
        $products = $products->orderBy(DB::raw('ISNULL(price), price'), 'ASC')->get();
        $parts = $products->pluck('part_id')->toArray();
        $groups = CatalogProduct::whereIn('id',$parts)->whereNotNull('product_group')->select('product_group')->distinct()->get();
        
        } else {
            $products = CatalogProduct::join('part_codes', function ($join) use ($type) { 
                                            $join->on('catalog_products.id', '=', 'part_codes.part_id')
                                                 ->where('part_codes.code', 'LIKE', preg_replace("/[^a-zA-Z0-9]+/","",$type));
                                    })
                                ->leftJoin(DB::raw('(SELECT product_id, min(price) as price FROM `catalog_price_product` group by product_id) as prices'), function ($join) { 
                                            $join->on('catalog_products.id', '=', 'prices.product_id');                                                 
                                    })
                                ->join('manufacturers',function ($join) {
                                            $join->on('catalog_products.manufacturer_id', '=', 'manufacturers.id')
                                                 ->where('manufacturers.active', '=', 'active');
                                    });
       
            $products = $products->select('catalog_products.*')
                                 ->where('catalog_products.active','active');
            $products = $products->orderBy('part_codes.sort')
                                 ->orderBy(DB::raw('ISNULL(price), price'), 'asc');
            $groups = $products->whereNotNull('product_group')->select('product_group')->distinct()->get();
        }
        return $groups;
    }

}