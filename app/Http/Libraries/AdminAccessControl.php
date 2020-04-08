<?php
namespace App\Http\Libraries;

use Illuminate\Support\Facades\Auth;
use App\Models\UserProfileSection;
use Illuminate\Routing\RouteCollection;
use Route;

Class AdminAccessControl{

	protected $route;

	public function __construct($route)
	{
		$this->route = $route;
	}

	public function setRoute($routeName)
	{
		$this->route = Route::getRoutes()->getByName($routeName);
	}

	public function hasPermission()
	{
		if ($this->checkIfItsUs()) return $this->checkIfItsUs();
		$profile = Auth::guard()->user()->profile;
		$segments = explode('.', $this->route->getName());
        $group = $segments[0];
        $permissions = UserProfileSection::whereProfile_id($profile->id)->whereGroup($group);
        if ($this->useMethod()) $permissions = $permissions->whereMethod($this->route->getActionMethod());
        if ($permissions->get()->count() > 0) return true;
        return false;

	}

	private function checkIfItsUs()
	{
		if(Auth::guard()->user()->email == 'contact@garageauto.ro') return true;
		return false;
	}

	private function useMethod()
	{
		if ($this->route->getActionMethod() == 'create') return true;
		if ($this->route->getActionMethod() == 'store') return true;
		if ($this->route->getActionMethod() == 'edit') return true;
		if ($this->route->getActionMethod() == 'update') return true;
		if ($this->route->getActionMethod() == 'destroy') return true;
		return false;
	}

}