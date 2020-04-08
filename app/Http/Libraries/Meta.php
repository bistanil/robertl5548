<?php

namespace App\Http\Libraries;
use App\Models\Staticmeta;
use App;

Class Meta{

	public static function build($page = null, $item = null)
	{
		$meta = (object)[];
		if ($page != null)
		{
			$meta = Staticmeta::whereLanguage(App::getLocale())->wherePage($page)->get()->first();
		}

		if ($item != null)
		{
			if (isset($item->meta_title)) $meta->meta_title = $item->meta_title;
			else $meta->meta_title = '';
			if (isset($item->meta_keywords)) $meta->meta_keywords = $item->meta_keywords;
			else $meta->meta_keywords = '';
			if (isset($item->meta_description)) $meta->meta_description = $item->meta_description;
			else $meta->meta_description = '';
		}

		return $meta;
	}

}