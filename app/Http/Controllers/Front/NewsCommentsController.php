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
use App\Models\Page;
use App\Models\NewsPost;
use App\Models\PostComment;
use App\Http\Requests\Front\PostCommentRequest;


class NewsCommentsController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

	public function store(PostCommentRequest $request)
	{
		$comment = new PostComment($request->all());
    	$comment->status = 'new';
    	if ($comment->save()) frontFlash()->success(trans('front/news.newsCommentSaveFlashTitle'), trans('front/news.newsCommentSaveFlashSuccessText'));
        else frontFlash()->error(trans('front/news.newsCommentSaveFlashTitle'), trans('front/news.newsCommentSaveFlashErrorText'));
        return back();
	}

}