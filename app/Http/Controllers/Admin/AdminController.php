<?php namespace App\Http\Controllers\Admin;

use App;
use Auth;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use App\Models\OfferRequest;
use App\Models\ContactMessage;
use App\Models\CarModelType;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\ReturnedProduct;
use App\Models\PostComment;
use JavaScript;
use URL;

class AdminController extends Controller {

	public function __construct()
	{
		$this->middleware('auth');
		JavaScript::put(['baseUrl' => URL::to('/')]);		
		
	}

	public function index()
	{			
		$breadcrumb='admin';
		$offerRequests = OfferRequest::whereStatus('new')->orderBy('id', 'desc')->limit(10)->get();
		$contactMessages = ContactMessage::whereStatus('new')->orderBy('id','desc')->limit(10)->get();
		$orders = Order::whereStatus('new')->orderBy('id', 'desc')->limit(10)->get();
		$reviews = ProductReview::whereStatus('new')->orderBy('id', 'desc')->limit(10)->get();
		$returnedProducts = ReturnedProduct::whereStatus('new')->orderBy('id','desc')->limit(10)->get();
		$comments = PostComment::whereStatus('new')->orderBy('id', 'desc')->limit(10)->get();
		return view('admin.partials.main.main', compact('breadcrumb', 'offerRequests', 'contactMessages', 'orders', 'reviews', 'returnedProducts', 'comments'));
	}

}