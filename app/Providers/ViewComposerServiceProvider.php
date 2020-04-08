<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App;
use DB;
use App\Models\Page;
use App\Models\Logo;
use App\Models\Contact;
use App\Models\SettingsEmail;
use App\Models\Banner;
use App\Models\Partner;
use App\Models\Socialmedia;
use App\Models\Catalog;
use App\Models\CatalogProduct;
use App\Models\ProductPrice;
use App\Models\ContactMessage;
use App\Models\OfferRequest;
use App\Models\Car;
use App\Models\CarModelGroup;
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\Order;
use App\Models\TransportMargin;
use App\Models\SettingsLink;
use App\Models\SettingsScript;
use App\Models\ReturnedProduct;
use App\Models\Feed;
use App\Models\PartsCategory;
use App\Models\CatalogCategory;
use App\Models\ProductReview;
use App\Models\Career;
use App\Models\Suggestion;
use App\Models\PostComment;
use App\Models\Company;
use App\Models\ClientDeleteRequest;
use JavaScript;
use Illuminate\Support\Facades\Request;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //Admin
        view()->composer('admin.partials.common.top', function($view){
            $contactMessages = ContactMessage::whereStatus('new')->orderBy('id','desc')->limit(20)->get();
            $view->with('contactMessages', $contactMessages);
            $suggestions = Suggestion::whereStatus('new')->orderBy('id','desc')->limit(20)->get();
            $view->with('suggestions', $suggestions);
            $offerRequests = OfferRequest::whereStatus('new')->orderBy('id','desc')->limit(20)->get();
            $view->with('offerRequests', $offerRequests);
            $orders = Order::whereStatus('new')->orderBy('id','desc')->limit(20)->get();
            $view->with('orders', $orders);
            $reviews = ProductReview::whereStatus('new')->orderBy('id','desc')->limit(20)->get();
            $view->with('reviews', $reviews);
            $returnedProducts = ReturnedProduct::whereStatus('new')->orderBy('id','desc')->limit(20)->get();
            $view->with('returnedProducts', $returnedProducts);
            $comments = PostComment::whereStatus('new')->orderBy('id', 'desc')->limit(10)->get();
            $view->with('comments', $comments);
            $clientDeleteRequests = ClientDeleteRequest::whereStatus('new')->orderBy('id','desc')->limit(20)->get();
            $view->with('clientDeleteRequests', $clientDeleteRequests);
        });

        view()->composer('admin.partials.common.sidebar', function($view){
            $activeFeeds = Feed::whereActive('active')->get(); 
            $view->with('activeFeeds', $activeFeeds);                                    
        });

        view()->composer('admin.partials.common.carSearch', function($view){
            $motorcycles = Car::whereActive('active')->whereType('motorcycle')->orderBy('title')->get();
            $trucks = Car::whereActive('active')->whereType('truck')->orderBy('title')->get();  
             $others= Car::whereActive('active')->where('type','other')->get();
             $view->with('others',$others);
            $view->with('motorcycles', $motorcycles);
            $view->with('trucks', $trucks);
            
            $cars = Car::whereActive('active')->whereType('car')->orderBy('title')->get();
            $view->with('cars', $cars);
            if (session()->has('type')) 
            {
                $groups = CarModelGroup::select('id')->where('car_id', session()->get('type')->model->modelsGroup->car->id)->whereActive('active')->get();
                $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->orderBy('title','ASC')->get();                
            } else $models = [];
            $view->with('models', $models);
            if (session()->has('type')) $types = CarModelType::whereModel_id(session()->get('type')->model_id)->whereActive('active')->orderBy('title','ASC')->get();
            else $types = [];
            $view->with('types', $types);
        });
        
        view()->composer('admin.partials.offers.offerPdf', function($view){
            $logo = Logo::whereActive('active')->whereLanguage(App::getLocale())->whereType('proforma')->get()->first(); 
            $view->with('logo', $logo);
            $contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first(); 
            $view->with('contactInfo', $contactInfo); 
            $company = Company::whereDefault('yes')->get()->first();   
            $view->with('company', $company);                                 
        });

        view()->composer('admin.partials.orders.proformaPdf', function($view){
            $logo = Logo::whereActive('active')->whereLanguage(App::getLocale())->whereType('proforma')->get()->first(); 
            $view->with('logo', $logo);
            $contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first(); 
            $view->with('contactInfo', $contactInfo);                                    
        });

        //Front

        view()->composer('front.partials.common.header', function($view){
            $logo = Logo::whereActive('active')->whereLanguage(App::getLocale())->whereType('favicon')->get()->first(); 
            $view->with('logo', $logo);
        });

        view()->composer('front.partials.common.logo', function($view){
            $logo = Logo::whereActive('active')->whereLanguage(App::getLocale())->whereType('front')->get()->first(); 
            $view->with('logo', $logo);
        });

        view()->composer('front.partials.common.menu', function($view){
            $pages = Page::whereActive('active')->whereLanguage(App::getLocale())->whereParent(0)->whereMenu('top')->orderBy('position', 'ASC')->get(); 
            $view->with('pages', $pages);
            $news = NewsPost::whereActive('active')->whereLanguage(App::getLocale())->orderBy('id')->limit(4)->get();
            $view->with('news', $news); 
            $catalogs = Catalog::whereActive('active')->whereLanguage(App::getLocale())->orderBy('position')->get(); 
            $view->with('catalogs', $catalogs); 
            $categories = PartsCategory::whereActive('active')->whereParent(1)->whereShow_in_menu('yes')->limit(5)->sorted()->get();
            $view->with('categories', $categories);
            $offers = CatalogProduct::whereActive('active')->whereOffer('yes')->count();
            $view->with('offers', $offers);
        });

        view()->composer('front.partials.common.sidebar.catalogs', function($view){
            $catalogs = Catalog::whereActive('active')->whereLanguage(App::getLocale())->orderBy('position')->get(); 
            $view->with('catalogs', $catalogs); 
        });

        view()->composer('front.partials.common.top', function($view){
            $contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first(); 
            $view->with('contactInfo', $contactInfo);
        });
        view()->composer('front.partials.common.cart', function($view){
            $catalogProduct = new CatalogProduct();
            $view->with('catalogProduct', $catalogProduct);
            $productPrice = new ProductPrice();
            $view->with('productPrice', $productPrice);                                 
        });

        view()->composer('front.partials.common.sidebar.categories', function($view){            
            if(session()->has('type'))
            {
                $type = session()->get('type');
                $categories = collect(DB::select(DB::raw("SELECT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent=1 AND type_categories.type_id=".$type->id." AND parts_categories.active = 'active' AND parts_categories.first_page = 'yes' ORDER BY position")));
            }
            else $categories = PartsCategory::whereActive('active')->whereParent(1)->whereFirst_page('yes')->sorted()->get();
            $view->with('categories', $categories);
        });

        view()->composer('front.partials.home.categories', function($view){            
            if(session()->has('type'))
            {
                $type = session()->get('type');
                $categories = collect(DB::select(DB::raw("SELECT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent=1 AND type_categories.type_id=".$type->id." AND parts_categories.active = 'active' AND parts_categories.first_page = 'yes' ORDER BY position")));
            }
            else $categories = PartsCategory::whereActive('active')->whereParent(1)->whereFirst_page('yes')->sorted()->get();
            $view->with('categories', $categories);
        });

        view()->composer('front.partials.home.parts', function($view){            
            if(session()->has('type'))
            {
                $type = session()->get('type');
                $products = CatalogProduct::join('type_parts', function ($join) use ($type) { 
                                            $join->on('catalog_products.id', '=', 'type_parts.part_id')
                                                 ->where('type_parts.type_id', '=', $type->id);
                                    })
                                ->select('catalog_products.*')
                                ->where('active','active')->where('first_page','yes')
                                ->inRandomOrder()->limit(8)->get();
            }
            else $products = CatalogProduct::where('catalog_id','=',0)->where('active','active')->where('first_page','yes')->inRandomOrder()->limit(8)->get();
            $view->with('products', $products);
        });

        view()->composer('front.partials.common.sidebar.car', function($view){
            $cars = Car::whereActive('active')->orderBy('title', 'ASC')->sorted()->get();
            $view->with('cars', $cars);
            if (session()->has('type')) 
            {
                $groups = CarModelGroup::select('id')->where('car_id', session()->get('type')->model->modelsGroup->car->id)->whereActive('active')->get();
                $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->sorted()->get();                
            } else $models = [];            
            $view->with('models', $models);
            if (session()->has('type')) {
                $fuels = CarModelType::select('fuel')->distinct()->whereModel_id(session()->get('type')->model_id)->whereActive('active')->get();
            } else $fuels = [];
            $view->with('fuels', $fuels);
            if (session()->has('type')) $types = CarModelType::whereModel_id(session()->get('type')->model_id)->whereActive('active')->sorted()->get();
            else $types = [];
            $view->with('types', $types);                                 
        });

        view()->composer('front.partials.common.search.selectCarWithFuel', function($view){
            $cars = Car::whereActive('active')->orderBy('title', 'ASC')->sorted()->get();
            $view->with('cars', $cars);
            if (session()->has('type')) 
            {
                $groups = CarModelGroup::select('id')->where('car_id', session()->get('type')->model->modelsGroup->car->id)->whereActive('active')->get();
                $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->sorted()->get();                
            } else $models = [];            
            $view->with('models', $models);
            if (session()->has('type')) {
                $fuels = CarModelType::select('fuel')->distinct()->whereModel_id(session()->get('type')->model_id)->whereActive('active')->get();
            } else $fuels = [];
            $view->with('fuels', $fuels);
            if (session()->has('type')) $types = CarModelType::whereModel_id(session()->get('type')->model_id)->whereActive('active')->sorted()->get();
            else $types = [];
            $view->with('types', $types);                                 
        });

        view()->composer('front.partials.home.banners', function($view){
            $banners = Banner::whereActive('active')->whereLanguage(App::getLocale())->whereType('general')->orderBy('position')->get(); 
            $view->with('banners', $banners);
        });

        view()->composer('front.partials.common.banners', function($view){
            $banners = Banner::whereActive('active')->whereLanguage(App::getLocale())->whereType('general')->orderBy('position')->get(); 
            $view->with('banners', $banners);
        });

        view()->composer('front.partials.common.brands', function($view){
            $brands = Car::whereActive('active')->whereFirstPage('yes')->orderBy('title')->get(); 
            $view->with('brands', $brands);
        });

        view()->composer('front.partials.common.partners', function($view){
            $partners = Partner::whereActive('active')->whereLanguage(App::getLocale())->orderBy('position')->get(); 
            $view->with('partners', $partners);
        });

         view()->composer('front.partials.common.brands', function($view){
            $brands = Car::whereActive('active')->whereFirstPage('yes')->whereLanguage(App::getLocale())->orderBy('position')->get(); 
            $view->with('brands', $brands);
        });

        view()->composer('front.partials.common.socialMedia', function($view){
            $socialMedia = Socialmedia::get()->first(); 
            $view->with('socialMedia', $socialMedia);
        });

        view()->composer('front.partials.products.common.show.contactInfo', function($view){
            $contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first(); 
            $view->with('contactInfo', $contactInfo);
        });

        view()->composer('front.partials.common.footer', function($view){
            $view->with('pages', Page::whereActive('active')
                ->whereParent(0)
                ->whereLanguage(App::getLocale())
                ->where(function($query){
                    $query->orWhere('menu', 'footer');
                    $query->orWhere('menu', 'terms');
                    $query->orWhere('menu', 'policy');
                    $query->orWhere('menu', 'cookies');
                    $query->orWhere('menu', 'warranty');
                    $query->orWhere('menu', 'return');
                })
                ->orderBy('position', 'ASC')->get());
            $contactInfo = Contact::whereLanguage(App::getLocale())->whereActive('active')->get()->first(); 
            $view->with('contactInfo', $contactInfo);
            $logo = Logo::whereActive('active')->whereLanguage(App::getLocale())->whereType('footer')->first();
            $view->with('logo', $logo);
            $links = SettingsLink::whereActive('active')->whereLanguage(App::getLocale())->get();
            $view->with('links', $links);
            $faqs = Page::whereActive('active')->whereLanguage(App::getLocale())->whereParent(0)->whereMenu('faq')->orderBy('position', 'ASC')->get(); 
            $view->with('faqs', $faqs);
            $partners = Page::whereActive('active')->whereLanguage(App::getLocale())->whereParent(0)->whereMenu('partners')->orderBy('position', 'ASC')->get(); 
            $view->with('partners', $partners);
            $suggestions = Page::whereActive('active')->whereLanguage(App::getLocale())->whereParent(0)->whereMenu('suggestions')->orderBy('position', 'ASC')->get(); 
            $view->with('suggestions', $suggestions);
        });

        view()->composer('front.partials.news.sidebar', function($view){
            $categories = NewsCategory::whereActive('active')->whereLanguage(App::getLocale())->orderBy('position')->get();
            $view->with('categories', $categories);
            $news = NewsPost::whereActive('active')->whereLanguage(App::getLocale())->orderBy('id', 'desc')->limit(5)->get();
            $view->with('news', $news);
        });

        view()->composer('front.partials.common.scripts.header', function($view){
            $script = SettingsScript::whereType('header')->whereActive('active')->get();
            $view->with('script', $script);
        });

        view()->composer('front.partials.common.scripts.body', function($view){
            $script = SettingsScript::whereType('body')->whereActive('active')->get();
            $view->with('script', $script);
        });

        view()->composer('front.partials.common.scripts.footer', function($view){
            $script = SettingsScript::whereType('footer')->whereActive('active')->get();
            $view->with('script', $script);
        });
        view()->composer('front.partials.common.scripts.socialShare', function($view){
            $script = SettingsScript::whereType('socialShare')->whereActive('active')->get();
            $view->with('script', $script);
        });

        view()->composer('front.partials.common.checkbox', function($view){
            $terms = Page::whereActive('active')->whereLanguage(App::getLocale())->whereMenu('terms')->get(); 
            $view->with('terms', $terms);
            $policy = Page::whereActive('active')->whereLanguage(App::getLocale())->whereMenu('policy')->get(); 
            $view->with('policy', $policy);
        });

        view()->composer('front.partials.common.scripts', function($view){
           $page = Page::whereActive('active')->whereMenu('cookies')->get()->first();
           if ($page != null) JavaScript::put(['cookiePolicyURL' => route('front-page', ['slug' => $page->slug]), 'cookieTitle' => $page->title ]);
           else JavaScript::put(['cookiePolicyURL' => route('front-home'), 'cookieTitle' => trans('front/cookiebar.title')]);
           JavaScript::put([
                'cookieMessage' => trans('front/cookiebar.message'),
                'cookieAccept' => trans('front/cookiebar.accept'),
                'cookieDecline' => trans('front/cookiebar.decline'),
            ]);
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
