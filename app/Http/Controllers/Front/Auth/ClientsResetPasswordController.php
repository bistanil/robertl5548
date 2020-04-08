<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Config;

class ClientsResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $broker = 'clients';
    protected $redirectTo = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $linkRequestView = 'emails.resetPassword';
    protected $resetView = 'front.clients.resetPassword';
    protected $guard = 'clients';

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        Config::set("auth.defaults.passwords","clients");
        $this->middleware('client');
    }
}