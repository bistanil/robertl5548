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
use Illuminate\Http\Request;
use App\Http\Libraries\Meta;
use App\Models\PriceMargin;
use App\Models\ProductPrice;
use App\Models\CatalogProduct;
use App\Models\TransportMargin;
use App\Http\Requests\Admin\CartRequest;
use App\Http\Libraries\Price;
use Cart;

class CartController extends Controller {

	public function __construct()
    {       
        JavaScript::put(['baseUrl' => URL::to('/')]);       
    }

	public function index(Request $request)
	{	
        session()->put('redirectToCheckout', url()->current());
        $transport = TransportMargin::where('min', '<=', Cart::instance('shopping')->total())->where('max', '>=', Cart::total())->get()->first();
        $breadcrumb = 'frontCart';
        $catalogProduct = new CatalogProduct();
        $productPrice = new ProductPrice();		
		$meta = Meta::build('cart');
		return view('front.partials.cart.main', compact('meta', 'breadcrumb', 'catalogProduct', 'productPrice', 'transport'));
	}

	/**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CartRequest $request)
    {
       $product = CatalogProduct::find($request->product_id);
       $price = ProductPrice::find($request->price);       
       $finalPrice = new Price();                
       $finalPrice = $finalPrice->discountedPrice($product, $price);
       session()->flash('cartPopup', true);
       session()->keep('partCodeSearch');
       if (Cart::instance('shopping')->add(['id' => $request->product_id, 'name' => productTitle($product), 'qty' => $request->qty, 'price' => $finalPrice, 'options' => ['priceId' => $price->id]])) frontFlash()->success(trans('front/cart.sendFlashTitle'), trans('front/cart.sendSuccessText'));
        else frontFlash()->error(trans('front/cart.sendFlashTitle'), trans('front/cart.sendErrorText'));
       //return redirect(route('front-cart'));
        return redirect()->back();
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
        foreach (Cart::instance('shopping')->content() as $key => $item) {
            $row = 'qty'.$item->rowId;
            Cart::instance('shopping')->update($item->rowId, $request->$row);
        }
        frontFlash()->success(trans('front/cart.editFlashTitle'), trans('front/cart.editSuccessText'));
        return redirect(route('front-cart'));
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
        Cart::instance('shopping')->remove($id);
        frontFlash()->success(trans('front/cart.deleteFlashTitle'), trans('front/cart.deleteSuccessText'));
        return redirect(route('front-cart'));
    }	

}