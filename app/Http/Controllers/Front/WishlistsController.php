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
use App\Http\Libraries\ClientWishlist;
use Cart;

class WishlistsController extends Controller {

	public function __construct()
    {       
        JavaScript::put(['baseUrl' => URL::to('/')]);       
    }

	public function index(Request $request)
	{	
		$request->session()->keep('categoryProductsUrl');
        $transport = TransportMargin::where('min', '<=', Cart::instance('wishlist')->total())->where('max', '>=', Cart::total())->get()->first();
        $breadcrumb='frontWishlist';
        $catalogProduct = new CatalogProduct();
        $productPrice = new ProductPrice();		
		$meta = Meta::build('home');
		return view('front.partials.wishlist.main', compact('meta', 'breadcrumb', 'catalogProduct', 'productPrice'));
	}

    public function transferToCart($rowId)
    {
        $item = Cart::instance('wishlist')->get($rowId);
        $product = CatalogProduct::find($item->id);
        $price = ProductPrice::find($item->options->priceId);
        $finalPrice = new Price();                
        $finalPrice = $finalPrice->discountedPrice($product, $price);
        Cart::instance('shopping')->add(['id' => $item->id, 'name' => productTitle($product), 'qty' => $item->qty, 'price' => $finalPrice, 'options' => ['priceId' => $item->options->priceId]]);
        Cart::instance('wishlist')->remove($rowId);
        if (Auth::guard('client')->check())
        {
            $wishlist = new ClientWishlist(Auth::guard('client')->user());
            $wishlist->persist();
        }
        return redirect(route('front-wishlist')); 
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
       $price = ProductPrice::find($request->price);       
       $finalPrice = new Price();                
       $finalPrice = $finalPrice->discountedPrice($product, $price);
       if (Cart::instance('wishlist')->add(['id' => $request->product_id, 'name' => $product->title, 'qty' => $request->qty, 'price' => $finalPrice, 'options' => ['priceId' => $price->id]])) frontFlash()->success(trans('front/wishlist.sendFlashTitle'), trans('front/wishlist.sendSuccessText'));
       else frontFlash()->error(trans('front/wishlist.sendFlashTitle'), trans('front/wishlist.sendErrorText'));
       if (Auth::guard('client')->check())
       {
           $wishlist = new ClientWishlist(Auth::guard('client')->user());
           $wishlist->persist();
       }
       return redirect(route('front-wishlist'));
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
        foreach (Cart::instance('wishlist')->content() as $key => $item) {
            $row = 'qty'.$item->rowId;
            Cart::instance('wishlist')->update($item->rowId, $request->$row);
        }
        frontFlash()->success(trans('front/wishlist.editFlashTitle'), trans('front/wishlist.editSuccessText'));
        if (Auth::guard('client')->check())
        {
            $wishlist = new ClientWishlist(Auth::guard('client')->user());
            $wishlist->persist();
        }
        return redirect(route('front-wishlist'));
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
        Cart::instance('wishlist')->remove($id);
        if (Auth::guard('client')->check())
        {
            $wishlist = new ClientWishlist(Auth::guard('client')->user());
            $wishlist->persist();
        }
        frontFlash()->success(trans('front/wishlist.deleteFlashTitle'), trans('front/wishlist.deleteSuccessText'));
        return redirect(route('front-wishlist'));
    }	

}