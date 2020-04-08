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
use App\Models\Offer;
use App\Models\Company;
use App\Models\OfferItem;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\Logo;
use App\Http\Requests\Admin\OfferRequest;
use App\Events\OfferDelete;
use App\Models\SettingsEmail;
use App\Notifications\SendOffer;
use JavaScript;
use URL;
use Cart;
use PDF;
use Mail;
use Notification;

class OffersController extends Controller
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
    public function index()
    {
        session()->put('adminItemsUrl',url()->full());
        $offers = Offer::orderByDesc('id')->paginate(session()->get('offersPerPage'));
        $breadcrumb='offers';
        return view('admin.partials.offers.main', compact('offers','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='offer.create';
        $catalogProduct = new CatalogProduct();
        $offerNr = Offer::max('id')+1;
        $productPrice = new ProductPrice();
        return view('admin.partials.offers.form', compact('breadcrumb', 'catalogProduct', 'offerNr', 'productPrice'));
    }

    public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('offerSearch',$request->q);
        $request->session()->keep('offerSearch');         
        $search = $request->session()->get('offerSearch');
        $offers = Offer::where('offers.name', 'LIKE', "%$search%")
                          ->orWhere('offers.email', 'LIKE', "%$search%")
                          ->orWhere('offers.phone', 'LIKE', "%$search%")
                          ->orWhere('offers.car', 'LIKE', "%$search%")
                          ->orWhere('offers.title', 'LIKE', "%$search%")
                          ->orWhere('offers.vin', 'LIKE', "%$search%")
                          ->paginate(session()->get('offersPerPage'));
        $breadcrumb='offers';
        return view('admin.partials.offers.search', compact('offers', 'breadcrumb', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OfferRequest $request)
    {
        $offer = new Offer($request->all());        
        $offer->total = Cart::instance('offerbox')->subtotal();
        $offer->currency = defaultCurrency();
        if ($offer->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        foreach (Cart::instance('offerbox')->content() as $key => $item)
        {
            $product = CatalogProduct::find($item->id);
            $offerItem = new OfferItem();
            $offerItem->offer_id = $offer->id;
            $offerItem->product_id = $item->id;
            $offerItem->product_title = $product->manufacturer->title.' '.$product->title.' '.$product->code;
            $offerItem->product_code = $product->code;
            $offerItem->unit_price = $item->price;
            $offerItem->currency = defaultCurrency();
            $offerItem->qty = $item->qty;
            $offerItem->price_id = $item->options->priceId;
            $offerItem->subtotal_unit_price = $item->subtotal;
            $offerItem->save();

        }
        Cart::instance('offerbox')->destroy();
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-offers');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Offer $offer)
    {
        $logo = Logo::whereType('proforma')->first();
        $company = Company::whereDefault('yes')->get()->first();
        $offer = $offer->find($id);
        $breadcrumb = 'offer.show';
        $item = $offer;
        return view('admin.partials.offers.show', compact('offer', 'breadcrumb', 'item', 'logo', 'company'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Offer $offer)
    {        
        $offer=$offer->find($id);
        if (!session()->has('offerEditId')) $this->populateOfferBox($offer);
        session()->put('offerEditId', $id);        
        $breadcrumb='offer.edit';
        $catalogProduct = new CatalogProduct();
        $productPrice = new ProductPrice();
        $item=$offer;
        return view('admin.partials.offers.updateForm', compact('offer','breadcrumb','item', 'catalogProduct', 'productPrice'));
    }

    private function populateOfferBox($offer)
    {
        $offerItems = OfferItem::whereOffer_id($offer->id)->get();
        Cart::instance('offerbox')->destroy();
        foreach ($offerItems as $key => $item) {
            Cart::instance('offerbox')->add([
                                                'id' => $item->product_id, 
                                                'name' => $item->product_title, 
                                                'qty' => $item->qty, 
                                                'price' => $item->unit_price, 
                                                'options' => ['priceId' => $item->price_id]
                                            ]);
        }        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(OfferRequest $request, $id, Offer $offer)
    {
        //
        $offer=$offer->find($id);
        $offer->total = Cart::instance('offerbox')->subtotal();
        $offer->currency = defaultCurrency();
        if ($offer->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        OfferItem::whereOffer_id($offer->id)->delete();
        foreach (Cart::instance('offerbox')->content() as $key => $item)
        {
            $product = CatalogProduct::find($item->id);
            $offerItem = new OfferItem();
            $offerItem->offer_id = $offer->id;
            $offerItem->product_id = $item->id;
            $offerItem->product_title = $product->manufacturer->title.' '.$product->title.' '.$product->code;
            $offerItem->product_code = $product->code;
            $offerItem->unit_price = $item->price;
            $offerItem->currency = defaultCurrency();
            $offerItem->qty = $item->qty;
            $offerItem->price_id = $item->options->priceId;
            $offerItem->subtotal_unit_price = $item->subtotal;
            $offerItem->save();

        }
        Cart::instance('offerbox')->destroy();
        session()->forget('offerEditId');
        return redirect('admin-offers');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Offer $offer)
    {
        //
        $offer=$offer->find($id);
        event(new OfferDelete($offer));
        if ($offer->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-offers');
    }

    public function offerPdf($id)
    {
        $company = Company::whereDefault('yes')->get()->first();
        $offer = Offer::find($id);
        if ($offer != null)
        {
            $pdf = PDF::loadView('admin.partials.offers.offerPdf', compact('offer', 'company'));
            return $pdf->download($offer->title.'.pdf');
        }
    }

    public function sendOffer(Request $request)
    {
        $offer = Offer::find($request->offer_id);
        Notification::send($offer, new SendOffer($offer));
        //$adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
        //Notification::send($adminEmail, new AdminSendOffer($offer));  
        flash()->success(trans('admin/offers.sentFlashTitle'), trans('admin/offers.sentSuccessText')); 
        return redirect('admin-offers');
    }
}
