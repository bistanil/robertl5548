<?php

namespace App\Http\Libraries;

Class Ancestors{

	protected $ancestors;

	public function create($item)
	{		
		$this->ancestors=[];		
		$this->getAncestors($item);
		return collect($this->ancestors)->reverse();
	}

	public function getAncestors($item)
	{
		array_push($this->ancestors, $item);
		$ancestor = $item::whereId($item->parent)->first();		
		if ($ancestor != null) $this->getAncestors($ancestor);
	}

}