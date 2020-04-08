<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use Session;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App;
use App\Models\Client;
use App\Models\ClientCars;
use App\Http\Requests\Admin\ClientCarRequest;
use JavaScript;
use URL;

class ClientCarsController extends Controller
{

    public function __construct(User $user)
    {
        $this->middleware('auth');
        JavaScript::put(['baseUrl' => URL::to('/')]); 

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug, Client $client)
    {
        $cars = $client->bySlug($slug)->cars()->paginate();
        $breadcrumb='adminClientCars';
        $item = $client->bySlug($slug);
        return view('admin.partials.clients.cars.main', compact('cars','breadcrumb', 'item'));
    }

}