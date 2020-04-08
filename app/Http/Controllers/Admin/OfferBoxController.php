<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Auth;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use App\User;
use App;
use App\Models\PriceMargin;
use App\Models\ProductPrice;
use App\Models\CatalogProduct;
use App\Http\Requests\Admin\OfferBoxRequest;
use App\Http\Libraries\Price;
use Cart;

class OfferBoxController extends Controller
{

    public function __construct(User $user)
    {
        $this->middleware('auth');              
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       $request->session()->keep('categoryProductsUrl');
       $breadcrumb='offers';
       $catalogProduct = new CatalogProduct();
       $productPrice = new ProductPrice();
       return view('admin.partials.offers.box.main', compact('breadcrumb', 'catalogProduct', 'productPrice'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OfferBoxRequest $request)
    {
       $request->session()->keep('categoryProductsUrl');
       $product = CatalogProduct::find($request->product_id);
       if (Cart::instance('offerbox')->add(['id' => $request->product_id, 'name' => $product->title, 'qty' => $request->qty, 'price' => $request->price, 'options' => ['priceId' => $request->price_id]])) flash()->success(trans('admin/offers.addFlashTitle'), trans('admin/offers.addSuccessText'));
        else flash()->error(trans('admin/offers.addFlashTitle'), trans('admin/offers.addErrorText'));
       return redirect('admin-offer-box');
    }

    public function reset(Request $request) 
    {
        if(session()->has('offerEditId')) $request->session()->forget('offerEditId');   
        Cart::instance('offerbox')->destroy();
        return redirect('admin-offer-box');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        foreach (Cart::instance('offerbox')->content() as $key => $item) {
            $qty = 'qty'.$item->rowId;
            Cart::instance('offerbox')->update($item->rowId, $request->$qty);            
            $price = 'price'.$item->rowId;
            if ($request->$price != $item->price) Cart::instance('offerbox')->update($item->rowId, ['options' => ['priceId' => -1]]);
            Cart::instance('offerbox')->update($item->rowId, ['price' => $request->$price]);
        }
        flash()->success(trans('admin/offers.editFlashTitle'), trans('admin/offers.editSuccessText'));
        return redirect('admin-offer-box');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        //
        if (Cart::instance('offerbox')->remove($id)) flash()->success(trans('admin/offers.deleteFlashTitle'), trans('admin/offers.deleteSuccessText'));
        else flash()->error(trans('admin/offers.deleteFlashTitle'), trans('admin/offers.deleteErrorText'));     
        return redirect('admin-offer-box');
    }
}
