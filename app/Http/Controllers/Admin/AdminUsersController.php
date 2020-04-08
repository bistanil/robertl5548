<?php namespace App\Http\Controllers\Admin;

use App;
use Auth;
use Session;
use Validator;
use App\User;
use App\Models\UserProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use JavaScript;
use URL;

class AdminUsersController extends Controller {

	public function __construct(User $user)
	{
		$this->middleware('auth');
		JavaScript::put(['baseUrl' => URL::to('/')]);	
	}

	public function index(User $user)
	{		
		$users= $user->visible();
		$breadcrumb='users';
		return view('admin.partials.users.main', compact('users','breadcrumb'));
	}

	public function create()
	{
		//$this->authorize('create', Auth::user());
		$profiles = UserProfile::all();
		$breadcrumb='user.create';
		return view('admin.partials.users.form', compact('breadcrumb', 'profiles'));
	}

	public function store(UserRequest $request)
	{
		$user = new User( $request->except('password','confirmPassword'));
		$user->password=bcrypt($request->password);		
		$user->visible='yes';
		$user->slug=str_slug($user->name, "-");
		$user->profile_id = $user->profile->id;
		if ($user->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
		else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
		return redirect('admin-users');
	}

	public function edit($slug, User $user)
	{
		$user=$user->bySlug($slug);
		$item=$user;
		$profiles = UserProfile::all();
		$breadcrumb='user.edit';
		return view('admin.partials.users.form', compact('user','breadcrumb','item', 'profiles'));
	}

	public function update($slug, UserRequest $request, User $user)
	{
		$user=$user->bySlug($slug);
		$user->slug=str_slug($request->name, "-");
		//$user->profile_id = $user->profile->id;
		if (!empty($request->password)) {
            if ($request->password === $request->password_confirmation) {
                $user->password = bcrypt($request->password);                
            }
        }
        if ($user->update($request->except('password','password_confirmation'))) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
		else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;		
		return redirect('admin-users');
	}

	public function destroy($slug, User $user)
	{
		$user=$user->bySlug($slug);
		if ($user->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
		else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));		
		return redirect('admin-users');
	}

}