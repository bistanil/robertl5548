<?php namespace App\Http\Controllers\Front;

use App;
use Auth;
use Session;
use Validator;
use JavaScript;
use DB;
use URL;
use App\Http\Controllers\Controller;
use App\Http\Libraries\Meta;
use Carbon\Carbon;
use App\Models\Career;
use App\Models\CareerApply;
use App\Models\SettingsEmail;
use App\Http\Requests\Front\CareerApplyRequest;
use App\Notifications\SendApplyCareerMessage;
use Mail;
use Notification;

class CareersController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

	public function index()
	{			
		$careers = Career::whereActive('active')->whereLanguage(App::getLocale())->paginate(5);
		$breadcrumb ='frontCareers';
		$meta = Meta::build('careers');
		return view('front.partials.careers.main', compact('meta','breadcrumb','careers'));
		 
    }

    public function career ($slug, Career $career)
	{			
		$career = $career->bySlug($slug);
		$meta = Meta::build(null, $career);
		$breadcrumb = 'frontCareer';
		$item = $career;
		return view('front.partials.careers.career', compact('meta', 'breadcrumb', 'career', 'item'));
	}

	public function sendApplyMessage(CareerApplyRequest $request)
	{
		$adminEmail = SettingsEmail::whereLanguage(App::getLocale())->whereActive('active')->whereDefault('yes')->get();
		$message = new CareerApply($request->all());
		$message->status = 'new';
		$message->docs = hwImage()->file($request, 'careerApply');
		Notification::send($adminEmail, new SendApplyCareerMessage($message)); 
    	if ($message->save()) frontFlash()->success(trans('front/careers.careerApplySaveFlashTitle'), trans('front/careers.careerApplySaveFlashSuccessText'));
        else frontFlash()->error(trans('front/careers.careerApplySaveFlashTitle'), trans('front/careers.careerApplySaveFlashErrorText'));
        return back();
	}

}