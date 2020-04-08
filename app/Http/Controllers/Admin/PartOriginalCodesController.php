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
use App\Models\CatalogProduct;
use JavaScript;
use URL;

class PartOriginalCodesController extends Controller
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
        $codes = $product->originalCodes()->paginate();
        $breadcrumb='partOriginalCodes';
        $item = $product;
        return view('admin.partials.parts.originalCodes.main', compact('codes','breadcrumb', 'item'));
    }

}