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
use App\Models\UserProfileSection;
use App\Models\UserProfile;
use App\Models\AccessControlSection;
use JavaScript;
use URL;
use Route;

class UserProfileSectionsController extends Controller
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
    public function index($profileId)
    {
        $sections = AccessControlSection::select('group', 'label', 'parent', 'show_actions')->distinct()->get();
        $profileSections = UserProfileSection::whereProfile_id($profileId)->get();        
        $profile = UserProfile::find($profileId);
        if ($profileSections == null) $profileSections = collect([]);
        $breadcrumb='userProfiles';
        return view('admin.partials.users.profiles.sections', compact('sections', 'profileSections', 'breadcrumb', 'profile'));       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $profileId)
    {
        $profile = UserProfile::find($profileId);
        if ($profile == null) {
            flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
            return redirect('admin-user-profiles');
        }
        UserProfileSection::whereProfile_id($profile->id)->delete();
        foreach ($request->ft_1 as $key => $item) {
            $segments = explode('.', $item);
            $profileSection = new UserProfileSection();
            $profileSection->profile_id = $profile->id;
            $profileSection->group = $segments[0];
            if (count($segments) > 1) $profileSection->method = $segments[1];
            $profileSection->authorised = 'yes';            
            $profileSection->save();
            if ($profileSection->method == 'create')
            {
                $profileSection2 = new UserProfileSection();
                $profileSection2->method = 'store';
                $profileSection2->profile_id = $profile->id;
                $profileSection2->group = $segments[0];            
                $profileSection2->authorised = 'yes';
                $profileSection2->save();
            }
            if ($profileSection->method == 'update')
            {
                $profileSection2 = new UserProfileSection();
                $profileSection2->method = 'edit';
                $profileSection2->profile_id = $profile->id;
                $profileSection2->group = $segments[0];            
                $profileSection2->authorised = 'yes';
                $profileSection2->save();
            }
        }
        flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));        
        return redirect('admin-user-profiles');
    }

}