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
use App\Models\PartCode;
use App\Models\CatalogProduct;
use DB;
use JavaScript;
use URL;

class PartEquivalencesController extends Controller
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
        $equivalences = PartCode::whereCode(prepCode($product->code))->paginate();                
        $breadcrumb='partEquivalences';
        $item = $product;
        return view('admin.partials.parts.equivalences.main', compact('equivalences','breadcrumb', 'item'));
    }

}