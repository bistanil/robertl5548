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
use App\Models\Exchange;
use App\Models\Currency;
use App\Http\Requests\Admin\ExchangeRateRequest;
use JavaScript;
use URL;

class ExchangeRatesController extends Controller
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
    public function index($id)
    {
        session()->put('adminItemsUrl',url()->full());
        $exchangeRates = Exchange::where('currency1', $id)->paginate();        
        $item = Currency::find($id);
        $breadcrumb='exchange';
        return view('admin.partials.currencies.exchange.main', compact('exchangeRates','breadcrumb', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $item = Currency::find($id);
        $currencies = Currency::where('id','!=', $id)->get();
        $breadcrumb='exchange.create';
        return view('admin.partials.currencies.exchange.form', compact('breadcrumb', 'item', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExchangeRateRequest $request, $id)
    {
        //
        $exchangeRate = new Exchange($request->all());
        $reverse = new Exchange();
        $reverse->currency1 = $exchangeRate->currency2;
        $reverse->currency2 = $exchangeRate->currency1;
        $reverse->rate = 1/$exchangeRate->rate;        
        if ($exchangeRate->save() && $reverse->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-exchange-rates.index', ['currencyId' => $id]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($currencyId, $id, Exchange $exchangeRate)
    {
        //
        $exchangeRate = $exchangeRate->find($id);
        $breadcrumb='exchange.edit';
        $item=$exchangeRate;
        $currencies = Currency::where('id','!=', $currencyId)->get();
        return view('admin.partials.currencies.exchange.form', compact('exchangeRate','breadcrumb','item', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ExchangeRateRequest $request, $currencyId, $id, Exchange $exchangeRate)
    {
        //
        $exchangeRate=$exchangeRate->find($id);
        $reverse=$exchangeRate->rate($exchangeRate->currency2, $exchangeRate->currency1);
        $reverse->rate = 1/$request->rate;
        if ($exchangeRate->update($request->all()) && $reverse->update()) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-exchange-rates.index', ['currencyId' => $currencyId]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($currencyId, $id, Exchange $exchangeRate)
    {
        //
        $exchangeRate=$exchangeRate->find($id);
        $reverse=$exchangeRate->rate($exchangeRate->currency2, $exchangeRate->currency1);
        if ($exchangeRate->delete() && $reverse->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-exchange-rates.index', ['currencyId' => $currencyId]));
    }
}