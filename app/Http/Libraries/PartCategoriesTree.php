<?php

namespace App\Http\Libraries;
use App\Models\PartsCategory;

Class PartCategoriesTree{

	protected $ids;
	protected $initialCategories;
	protected $isSearch;

	public function __construct($initialCategories, $isSearch = false)
	{
		$this->ids = [];
		$this->initialCategories = $initialCategories;
		$this->isSearch = $isSearch;
	}

	public function buildTree()
	{
		$this->process();
		return PartsCategory::whereIn('id', $this->ids)->get();		
	}

	private function process()
	{
		foreach ($this->initialCategories as $key => $category) {
			$this->ids[count($this->ids)+1] = (int)$category->id;
			$this->addParents($category);
		}
	}

	private function addParents($category)
	{		
		$category = PartsCategory::find($category->parent);
		if ($category != null)
		{
			//if ($category->parent == 1) dd($category);
			if ($this->isSearch) if (in_array($category->id, $this->ids) == FALSE && $category->parent == 1) $this->ids[count($this->ids)+1] = (int)$category->id;
			//else if (in_array($category->id, $this->ids) == FALSE) $this->ids[count($this->ids)+1] = (int)$category->id;
			if ($category->parent > 0) $this->addParents($category);
		}
	}

}