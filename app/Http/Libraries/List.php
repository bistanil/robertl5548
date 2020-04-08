<?php

namespace App\Http\Libraries;

Class List {

	public function active()
	{
		return [
			'active' => trans('admin.common.active'),
			'inactive' => trans('admin.common.inactive')
		];
	}

	

}