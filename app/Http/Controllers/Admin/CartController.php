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
use App\Models\TransportMargin;
use App\Models\Currency;
use App\Http\Requests\Admin\CartRequest;
use App\Http\Libraries\Price;
use Cart;
use JavaScript;
use URL;

class CartController extends Controller
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
    public function index(Request $request)
    {
       $request->session()->keep('categoryProductsUrl');
       $transport = TransportMargin::where('min', '<=', Cart::total())->where('max', '>=', Cart::total())->get()->first();
       $breadcrumb='cart';
       $catalogProduct = new CatalogProduct();
       $productPrice = new ProductPrice();
       return view('admin.partials.cart.main', compact('breadcrumb', 'catalogProduct', 'productPrice', 'transport'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CartRequest $request)
    {
       $request->session()->keep('categoryProductsUrl');
       $product = CatalogProduct::find($request->product_id);
       if($request->price != 1) {
            $price = ProductPrice::find($request->price); 
            $finalPrice = new Price();                
            $finalPrice = $finalPrice->finalPrice($product, $price);  
            $priceId = $price->id;   
       } else {
            $finalPrice = $request->price;    
            $priceId = 0;
       }
       if (Cart::add(['id' => $request->product_id, 'name' => productTitle($product), 'qty' => $request->qty, 'price' => $finalPrice, 'options' => ['priceId' => $priceId]])) flash()->success(trans('admin/cart.addFlashTitle'), trans('admin/cart.addSuccessText'));
        else flash()->error(trans('admin/cart.addFlashTitle'), trans('admin/cart.addErrorText'));
       return redirect('admin-cart');
    }

    public function resetCart(Request $request) 
    {
        if(session()->has('editOrderId')) $request->session()->forget('editOrderId');   
        return redirect('admin-cart');
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
        foreach (Cart::content() as $key => $item) {
            $qtyLabel = 'qty'.$item->rowId;
            Cart::update($item->rowId, ['qty' => $request->$qtyLabel]);
            $this->checkTempPrice($item, $request);                                   
        }        
        flash()->success(trans('admin/cart.editFlashTitle'), trans('admin/cart.editSuccessText'));
        return redirect('admin-cart');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $item = Cart::get($id);
        ProductPrice::whereSource('temporary')->whereProduct_id($item->id)->delete();
        if (Cart::remove($id)) flash()->success(trans('admin/cart.deleteFlashTitle'), trans('admin/cart.deleteSuccessText'));
        else flash()->error(trans('admin/cart.deleteFlashTitle'), trans('admin/cart.deleteErrorText'));     
        return redirect('admin-cart');
    }

    public function checkTempPrice($cartItem, $request)
    {
        $product = CatalogProduct::find($cartItem->id);
        $price = ProductPrice::find($cartItem->options->priceId);
        if ($price == null) $price = new ProductPrice();   
        else if($price->source != 'temporary') $price = new ProductPrice();
        
        
        $priceLabel = 'price'.$cartItem->rowId;        
        if (floatval(finalPrice($product)) != floatval($request->$priceLabel)) $this->createItemTemporaryPrice($cartItem, $price, $request->$priceLabel);

    }

    public function createItemTemporaryPrice($cartItem, $price, $priceValue)
    {        
        $currency = Currency::whereDefault('yes')->get()->first();        
        $price->currency_id = $currency->id;
        $price->product_id=$cartItem->id;  
        $price->price = $priceValue;    
        $price->source='temporary';
        $price->save(); 
        Cart::update($cartItem->rowId, ['price' => $priceValue, 'options' => ['priceId' => $price->id]]);                     
    }    

}