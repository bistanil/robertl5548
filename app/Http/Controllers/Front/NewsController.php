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
use App\Models\NewsCategory;
use App\Models\PostImage;
use App\Models\PostCategory;
use App\Models\PostComment;


class NewsController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

	public function index()
	{			
		$news = NewsPost::whereActive('active')->whereLanguage(App::getLocale())->paginate(5);
		$breadcrumb = 'frontNews';
		$meta = Meta::build('blog');
		$postCategories = PostCategory::get();
		return view('front.partials.news.main', compact('meta','breadcrumb','news','postCategories'));
		 
    }

    public function post($slug)
	{			
		$post = NewsPost::whereSlug($slug)->get()->first();
		$meta = Meta::build(null, $post);
		$breadcrumb = 'frontPost';
		$item = $post;
		return view('front.partials.news.post', compact('meta', 'breadcrumb', 'post', 'item'));
	}

    public function categoryNews($slug)
    {     
        $parent = NewsCategory::whereSlug($slug)->get()->first();
        $news = $parent->posts()->paginate();
        $breadcrumb = 'frontNews';
        $item = $parent;
        $meta = Meta::build('blog');              
        return view('front.partials.news.main', compact('news','breadcrumb','item','parent','meta'));
    }

}