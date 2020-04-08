<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Libraries\Meta;
use App\Models\Client;
use App\Http\Libraries\AccountCreation;
use App\Http\Requests\Front\ClientRequest;
use Auth;
use Carbon\Carbon;
use JavaScript;
use URL;
use DB;
use App;
use Session;

class ClientsLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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

    protected function guard()
    {
        return Auth::guard('client');
    }

    public function getLogin()
    {   
        $breadcrumb='frontLogin';
        $meta = Meta::build('login');
        return view('front.partials.clients.login', compact('meta','breadcrumb'));
    }

    public function getLogout()
    {
        Auth::guard('client')->logout();
        return redirect(route('client-login'));
    }
}
