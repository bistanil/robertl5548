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
use App\Models\CarModelType;
use App\Models\PartsCategory;
use App\Models\CatalogProduct;
use App\Models\TypePart;
use DB;
use JavaScript;
use URL;

class TypeCategoriesController extends Controller
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
    public function index($slug, CarModelType $type)
    {
        $type = $type->bySlug($slug);
        $categories = collect(DB::select(DB::raw('SELECT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent=0 AND type_categories.type_id='.$type->id)));
        $breadcrumb='carTypeCategories.index';
        $item = $type;
        return view('admin.partials.parts.types.categories', compact('categories', 'type','breadcrumb', 'item'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function subcategories($typeSlug, CarModelType $type, $categorySlug, PartsCategory $category)
    {
        $type = $type->bySlug($typeSlug);
        $category = $category->bySlug($categorySlug);
        $categories = collect(DB::select(DB::raw('SELECT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent='.$category->id.' AND type_categories.type_id='.$type->id)));

        if ($categories->count() > 0)
        {
            $breadcrumb='carTypeCategories.index';
            $item = $type;
            return view('admin.partials.parts.types.categories', compact('categories', 'type','breadcrumb', 'item'));
        } else return redirect()->route('admin-type-category-products.index', ['typeSlug'=> $type->slug, 'categorySlug' => $category->slug]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function products($typeSlug, CarModelType $type, $categorySlug, PartsCategory $category)
    {
        $request = Request();
        Session::flash('backUrl', $request->fullUrl());
        $type = $type->bySlug($typeSlug);
        $category = $category->bySlug($categorySlug);        
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
                                    })                                
                                ->select('type_parts.part_id')
                                ->distinct();        
        $products = $products->orderBy('price')->paginate();        
        $breadcrumb='carTypeCategoryProducts.index';
        $item = new \stdClass;
        $item->type = $type;
        $item->category = $category;
        return view('admin.partials.parts.types.products', compact('products', 'breadcrumb', 'item'));        
    }

}