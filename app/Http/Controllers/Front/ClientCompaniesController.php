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
use App\Models\ClientCompany;
use App\Http\Requests\Front\ClientCompanyRequest;

class ClientCompaniesController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

    public function index()
	{	
		$client = Auth::guard('client')->user();
		$companies = ClientCompany::whereClient_id($client->id)->paginate();
		$meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientCompanies';        
        return view('front.partials.clients.companies.main', compact('meta', 'breadcrumb', 'client', 'companies'));
	}

	public function create()
	{
		$meta = Meta::build('ClientAccount');
		$breadcrumb = 'frontClientCompanyCreate';
		return view('front.partials.clients.companies.form', compact('meta', 'breadcrumb'));
	}

	public function store(ClientCompanyRequest $request, ClientCompany $company)
	{
		$company = new ClientCompany($request->all());
		$company->client_id = Auth::guard('client')->user()->id;
		if ($company->save()) frontFlash()->success(trans('front/common.addFlashTitle'), trans('front/common.addSuccessText'));
		else frontFlash()->error(trans('front/common.addFlashTitle'), trans('front/common.addErrorText'));
		return redirect(route('front-client-companies'));
	}

	public function edit($id, ClientCompany $company)
	{
		$company = $company->find($id);
		$meta = Meta::build('ClientAccount');
		$breadcrumb = 'frontClientCompanyEdit';
		$item = $company;
		return view('front.partials.clients.companies.form', compact('meta', 'breadcrumb', 'company', 'item'));
	}

	public function update(ClientCompany $company, ClientCompanyRequest $request)
	{
		$company = $company->find($request->id);
		if ($company->update($request->except('id'))) frontFlash()->success(trans('front/common.editFlashTitle'), trans('front/common.editSuccessText'));
        else frontFlash()->error(trans('front/common.editFlashTitle'), trans('front/common.editErrorText'));
        return redirect(route('front-client-companies'));
	}

	public function destroy($id, ClientCompany $company)
	{
		$company = $company->find($id);
		if ($company->delete()) frontFlash()->success(trans('front/common.deleteFlashTitle'), trans('front/common.deleteSuccessText'));
		else frontFlash()->error(trans('front/common.deleteFlashTitle'), trans('front/common.deleteErrorText'));
		return redirect(route('front-client-companies'));
	}

}