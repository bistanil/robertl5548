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
use App\Models\Newsletter;
use App\Http\Requests\Front\NewsletterRequest;

class NewsletterController extends Controller {

    public function __construct()
    {       
        JavaScript::put(['baseUrl' => URL::to('/')]);       
    }

    public function store(NewsletterRequest $request)
    {
        $subscription = new Newsletter($request->all());
        $subscription->active = 'active';
        session()->flash('newsletter', true);
        if ($subscription->save()) frontFlash()->success(trans('front/common.newsletterFlashTitle'), trans('front/common.newsletterFlashSuccessText'));
        else frontFlash()->error(trans('front/common.newsletterFlashTitle'), trans('front/common.newsletterFlashErrorText'));
        return redirect(route('front-home'));
    }
}