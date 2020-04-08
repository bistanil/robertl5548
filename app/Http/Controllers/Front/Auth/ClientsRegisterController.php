<?php

namespace App\Http\Controllers\Front\Auth;

use App;
use Auth;
use Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Libraries\Meta;
use App\Models\Client;
use App\Models\Page;
use App\Http\Libraries\ClientManager;
use App\Http\Requests\Front\ClientRequest;
use Carbon\Carbon;
use JavaScript;
use URL;
use DB;

class ClientsRegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = ''; 

    protected $guard = 'client';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('client', ['except' => ['getLogout', 'login', 'register']]);        
        JavaScript::put(['baseUrl' => URL::to('/')]);       
        $this->redirectTo = route('client-account');
    }

    public function showRegister()
    {   
        $breadcrumb='frontRegister';
        $meta = Meta::build('register');
        $pages = Page::whereActive('active')->whereLanguage(App::getLocale())->whereParent(0)->whereMenu('terms')->get();
        return view('front.partials.clients.register', compact('meta','breadcrumb', 'pages'));
    }

    public function store(ClientRequest $request)
    {
        $client = new ClientManager($request);
        $client = $client->get();
        if ($client == null)
        {
           frontFlash()->info(trans('front/clients.registerUserExistsTitle'), trans('front/clients.registerUserExistsText'));
           return redirect(route('client-login'));
        }  
        Auth::guard('client')->login($client, true);
        return redirect(route('client-account'));
    }
}
