<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App;
use Cart;
use JavaScript;
use URL;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\TransportMargin;
use App\Models\Client;
use App\Models\Discount;
use App\Models\Currency;
use App\Models\Contact;
use App\Models\Logo;
use App\Http\Requests\Admin\OrderRequest;
use App\Http\Libraries\Discounter;
use App\Http\Libraries\PlaceOrder;
use PDF;

class ProformasController extends Controller
{


    /**
     * Return order proforma in PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pdf($id)
    {
        $contactInfo = Contact::whereLanguage(App::getLocale())->get()->first();
        $order = Order::find($id);
        $logo = Logo::whereType('proforma')->whereLanguage(locale())->get()->first();
        if ($order != null)
        {
            $headerHtml = view()->make('admin.partials.orders.proforma.header', compact('logo','order', 'contactInfo'))->render();
            $footerHtml = view()->make('admin.partials.orders.proforma.footer', compact('logo','order', 'contactInfo'))->render();
            $pdf = PDF::loadView('admin.layouts.proforma', compact('logo','order', 'contactInfo'))
               ->setPaper('a4')
               ->setOption('margin-top', '30mm')
               ->setOption('margin-bottom', '30mm')
               ->setOption('header-html', $headerHtml)
               ->setOption('footer-html', $footerHtml);
            return $pdf->download(trans('admin/orders.proforma').'-'.$order->id.'.pdf');
        }
    }
}