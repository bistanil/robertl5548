<?php namespace App\Http\Controllers\Admin;

use App;
use Auth;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use Spatie\Analytics\Period;
use JavaScript;
use Carbon\Carbon;
use DB;
use URL;
use Analytics;

class AnalyticsController extends Controller {

	public function __construct()
	{
		$this->middleware('auth');		
		JavaScript::put(['baseUrl' => URL::to('/')]);
	}

	public function index()
	{			
		$breadcrumb='analytics';
		$analyticsData = Analytics::fetchTotalVisitorsAndPageViews(Period::days(30));
		$dates=[];
			foreach ($analyticsData as $key => $item) {
				$item=collect($item);
				$dates[]=$item['date']->format('d.m');
			}
		$visitors=[];
		$pageViews=[];
			foreach ($analyticsData as $key => $item) 
			{
				$visitors[]=$item['visitors'];
				$pageViews[]=$item['pageViews'];
			}
		$endDate = Carbon::now();
		$startDate = new Carbon('last month');
		$metrics = 'ga:sessions';		
		$new=[];
		$returning=[];
		$monthDates=[];
		while ( $startDate < $endDate) 
		{
			$item = Analytics::performQuery(Period::create($startDate, $startDate->addDay()), $metrics, $others = array('dimensions' => 'ga:userType'));			
			$new[]=$item->rows[0][1];
			if (isset($item->rows[1][1])) $returning[]=$item->rows[1][1];
			else $returning[]=0;			
			$monthDates[]=$startDate->format('d.m');
		}
		JavaScript::put([
	        'chartLabels' => $dates,
	        'chartVisitors' => $visitors,
	        'chartPageviews' => $pageViews,
	        'nvrLabels' => $monthDates,
	        'nvrNew' => $new,
	        'nvrReturning' => $returning
	    ]);	
		return view('admin.partials.analytics.main', compact('breadcrumb'));
	}
}