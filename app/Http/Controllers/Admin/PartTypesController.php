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
use App\Models\CatalogProduct;
use App\Models\TypePart;
use App\Models\Car;
use JavaScript;
use URL;

class PartTypesController extends Controller
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
    public function index($slug, CatalogProduct $product)
    {
        $product = $product->bySlug($slug);
        $types = $product->types()->orderBy('id')->paginate(session()->get('typesPerPage'));
        $breadcrumb='partTypes';
        $item = $product;
        return view('admin.partials.parts.types.main', compact('types','breadcrumb', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug)
    {
        $product = CatalogProduct::whereSlug($slug)->get()->first();
        $cars = Car::all();
        $breadcrumb='partTypes.create';
        $item = $product;
        return view('admin.partials.parts.types.form', compact('breadcrumb', 'cars', 'product', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = CatalogProduct::find($request->product_id);
        if ($product != null && $request->has('types'))
        {
            foreach ($request->types as $key => $typeId) {
                if (intval($typeId > 0)){
                    $link = new TypePart();
                    $link->type_id = $typeId;
                    $link->part_id = $product->id;
                    $link->save();
                }
            }
            if (count($request->types > 0)) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
            else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        } else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect(route('admin-part-types', ['slug' => $product->slug]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $product = CatalogProduct::find($request->productId);
        if (TypePart::whereType_id($request->typeId)->wherePart_id($request->productId)->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));   
        return redirect(route('admin-part-types', ['slug' => $product->slug]));
    }

}