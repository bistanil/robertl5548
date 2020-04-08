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
use App\Models\ProductReview;
use App\Http\Requests\Front\ProductReviewRequest;

class ReviewsController extends Controller {

    public function __construct()
    {       
        JavaScript::put(['baseUrl' => URL::to('/')]);       
    }

    public function store(ProductReviewRequest $request)
    {
    	$review = new ProductReview($request->all());
    	if (Auth::guard('client')->check())
    	{
            $review->client_id = Auth::guard('client')->user()->id;
    		$review->name = Auth::guard('client')->user()->name;
    		$review->email = Auth::guard('client')->user()->email;
    	}
        $review->status = 'new';
    	if ($review->save()) frontFlash()->success(trans('front/catalogs.reviewSaveFlashTitle'), trans('front/catalogs.reviewSaveFlashSuccessText'));
        else frontFlash()->error(trans('front/catalogs.reviewSaveFlashTitle'), trans('front/catalogs.reviewSaveFlashErrorText'));
        return back();
    }
}