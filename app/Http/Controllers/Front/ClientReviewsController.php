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
use App\Http\Libraries\Meta;
use App\Models\Client;
use App\Models\ProductReview;
use App\Http\Requests\Front\ProductReviewRequest;

class ClientReviewsController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

    public function index()
	{	
		$client = Auth::guard('client')->user();
		$reviews = ProductReview::whereClient_id($client->id)->paginate();
		$meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientReviews';        
        return view('front.partials.clients.reviews.main', compact('meta', 'breadcrumb', 'client', 'reviews'));
	}

	public function create()
	{
		$meta = Meta::build('ClientAccount');
		$breadcrumb = 'frontClientCompanyCreate';
		return view('front.partials.clients.reviews.form', compact('meta', 'breadcrumb'));
	}

	public function store(ProductReviewRequest $request, ProductReview $review)
	{
		$review = new ProductReview($request->all());
		$review->client_id = Auth::guard('client')->user()->id;
		if ($review->save()) frontFlash()->success(trans('front/common.addFlashTitle'), trans('front/common.addSuccessText'));
		else frontFlash()->error(trans('front/common.addFlashTitle'), trans('front/common.addErrorText'));
		return redirect(route('front-client-reviews'));
	}

	public function edit($id, ProductReview $review)
	{
		$review = $review->find($id);
		$meta = Meta::build('ClientAccount');
		$breadcrumb = 'frontClientReviewEdit';
		$item = $review;
		return view('front.partials.clients.reviews.form', compact('meta', 'breadcrumb', 'review', 'item'));
	}

	public function update(ProductReview $review, ProductReviewRequest $request)
	{
		$review = $review->find($request->id);
		$review->status = 'new';
		if ($review->update($request->except('id'))) frontFlash()->success(trans('front/common.editFlashTitle'), trans('front/common.editSuccessText'));
        else frontFlash()->error(trans('front/common.editFlashTitle'), trans('front/common.editErrorText'));
        return redirect(route('front-client-reviews'));
	}

	public function destroy($id, ProductReview $review)
	{
		$review = $review->find($id);
		if ($review->delete()) frontFlash()->success(trans('front/common.deleteFlashTitle'), trans('front/common.deleteSuccessText'));
		else frontFlash()->error(trans('front/common.deleteFlashTitle'), trans('front/common.deleteErrorText'));
		return redirect(route('front-client-reviews'));
	}

}