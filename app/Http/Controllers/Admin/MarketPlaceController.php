<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use Session;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateFeedRequest;
use App\Http\Requests\Admin\ProductListRequest;
use App\Http\Libraries\GetProductsFeed;
use App\Models\Manufacturer;
use App\Models\CatalogProduct;
use App\Models\Feed;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Models\FeedProduct;
use App\User;
use App;
use Excel;
use Artisan;
use JavaScript;
use URL;
use Illuminate\Support\Facades\Storage;


class MarketPlaceController extends Controller
{

    public function __construct(User $user)
    {
        $this->middleware('auth');    
        JavaScript::put(['baseUrl' => URL::to('/')]);           
    }

    public function index()
    {
        $feeds = FeedProduct::orderBy('id', 'desc')->paginate(15);
        $breadcrumb = 'productFeeds';
        return view('admin.partials.catalogs.feeds.main', compact('breadcrumb', 'feeds'));
    }

    public function create()
    {
        $feeds = Feed::whereActive('active')->get();
        $manufacturers = Manufacturer::whereActive('active')->get();
        $cars = Car::whereActive('active')->get();
        $models = [];
        //$types = [];
        $breadcrumb = 'createProductFeeds';
        return view('admin.partials.catalogs.feeds.form', compact('feeds', 'breadcrumb', 'manufacturers', 'cars', 'models'));
    }

    public function store(GenerateFeedRequest $request)
    {
        $feed = new FeedProduct();
        $feed->file_name = $request->file_name;
        $feed->feed_id = $request->feed_id;
        $feed->type = $request->type;
        $feed->catalogs = json_encode($request->catalogs, JSON_NUMERIC_CHECK );
        $feed->description = $request->description;
        $feed->description_title = $request->description_title;
        $feed->cars = json_encode($request->cars, JSON_NUMERIC_CHECK );
        $feed->models = json_encode($request->models, JSON_NUMERIC_CHECK );
        $feed->manufacturer_id = json_encode($request->manufacturer_id, JSON_NUMERIC_CHECK );
        $feed->feed_prices = $request->feed_prices;
        $feed->min_price = $request->min_price;
        $feed->max_price = $request->max_price;
        $feed->okazii_extra_margin = $request->okazii_extra_margin;
        $feed->okazii_payment_info = $request->okazii_payment_info;
        $feed->okazii_delivery_info = $request->okazii_delivery_info;
        $feed->okazii_return_info = $request->okazii_return_info;
        $feed->use_additional_title = $request->use_additional_title;
        if($feed->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect(route('admin-create-feed'));
    }

    public function edit($id, FeedProduct $feed)
    {
        $feed = $feed->find($id);
        $feeds = Feed::whereActive('active')->get();
        //dd(json_decode($feed->manufacturer_id));
        $manufacturers = Manufacturer::whereActive('active')->get();
        $feedManufacturers = Manufacturer::whereIn('id', (array) json_decode($feed->manufacturer_id))->pluck('id')->toArray();
        $feedCars = Car::whereIn('id', (array) json_decode($feed->cars))->pluck('id')->toArray();
        $feedModels = CarModel::whereIn('id', (array) json_decode($feed->models))->pluck('id')->toArray();
        $cars = Car::whereActive('active')->get();
        if($feedCars) {
        $models = CarModel::Join('car_model_groups', function ($join){
                    $join->on('car_model_groups.id', '=', 'car_models.model_group_id');
                  })
                  ->Join('cars', function ($join) use($feed){
                    $join->on('cars.id', '=', 'car_model_groups.car_id')
                        ->whereIn('cars.id',json_decode($feed->cars));
                  })
                  ->select('car_models.*')
                  ->where('car_models.active', 'active')
                  ->get();
        } else {
            $models = [];
        }
        //$types = CarModelType::whereIn('model_id', json_decode($feed->models))->get();
        
        $breadcrumb = 'editProductFeeds';
        $item = $feed;
        return view('admin.partials.catalogs.feeds.form', compact('feeds', 'breadcrumb', 'manufacturers', 'cars', 'models', 'types', 'feed', 'item', 'feedCars', 'feedModels', 'feedTypes', 'feedManufacturers'));
    }

    public function update($id, FeedProduct $feed, GenerateFeedRequest $request)
    {
        $feed = $feed->find($id);
        $feed->file_name = $request->file_name;
        $feed->feed_id = $request->feed_id;
        $feed->type = $request->type;
        $feed->catalogs = json_encode($request->catalogs, JSON_NUMERIC_CHECK );
        $feed->description = $request->description;
        $feed->description_title = $request->description_title;
        $feed->cars = json_encode($request->cars, JSON_NUMERIC_CHECK );
        $feed->models = json_encode($request->models, JSON_NUMERIC_CHECK );
        $feed->manufacturer_id = json_encode($request->manufacturer_id, JSON_NUMERIC_CHECK );
        $feed->feed_prices = $request->feed_prices;
        $feed->min_price = $request->min_price;
        $feed->max_price = $request->max_price;
        $feed->okazii_extra_margin = $request->okazii_extra_margin;
        $feed->okazii_payment_info = $request->okazii_payment_info;
        $feed->okazii_delivery_info = $request->okazii_delivery_info;
        $feed->okazii_return_info = $request->okazii_return_info;
        if($feed->update($feed->toArray())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;     
        return redirect(route('admin-create-feed'));
    }

    public function destroy($id, FeedProduct $feed)
    {
        $feed = $feed->find($id);
        Storage::disk('feeds')->delete($feed->file_name.'.'.$feed->feed->file_extension);
        if($feed->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        return redirect(route('admin-create-feed'));
    }

    public function feedGenerate($id)
    {
        //chdir(base_path());
        //dd('php artisan createproductsfeed '.Auth::user()->email.' '.$id);
        exec('bash -c "exec nohup setsid php artisan create-products-feed '.Auth::user()->email.' '.$id.' > /dev/null 2>&1 &"');
        //Artisan::call('createproductsfeed', ['email' => Auth::user()->email, 'id' => $id]);
        flash()->success(trans('admin/feeds.generateFeeds'), trans('admin/feeds.generateFeedContent'));
        return redirect(route('admin-create-feed'));     
    }

    public function download()
    {
        $feeds = FeedProduct::paginate(15);
        $breadcrumb = 'productFeeds';
        $files = Storage::files('public/files/feeds');
        return view('admin.partials.catalogs.feeds.download', compact('breadcrumb', 'feeds', 'files'));
    }

    public function productListExportForm()
    {
        $breadcrumb = 'admin';
        return view('admin.partials.catalogs.feeds.productListForm', compact('breadcrumb'));
    }

    public function productListExport(ProductListRequest $request)
    {
        exec('bash -c "exec nohup setsid php artisan generateproductnomenclature '.$request->with_prices.' > /dev/null 2>&1 &"');
        flash()->success(trans('admin/feeds.generateFeeds'), trans('admin/feeds.generateProductListContent'));
        return redirect(route('admin'));     
    }

}