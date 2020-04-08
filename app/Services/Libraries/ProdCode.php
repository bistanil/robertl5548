<?php namespace App\Services\Libraries;

use App;
use App\Models\PartCode;

class ProdCode{

	public function store($request, $product)
	{
		$status = true;
		$status = $this->saveOwnCode($product);
		//foreach (explode(',', $request['other_codes']) as $key => $code) $status = $this->saveOtherCode($product, $code);
		return $status;
	}

	public function update($request, $product)
	{
		$this->destroy($product);
		return $this->store($request, $product);
	}

	public function destroy($product)
	{
		return PartCode::wherePart_id($product->id)->delete();
	}

	private function saveOwnCode($product)
	{
		$link = new PartCode();
		$link->code = preg_replace("/[^a-zA-Z0-9]+/","", $product->code);
		$link->part_id = $product->id;
		return $link->save();
	}

	private function saveOtherCode($product, $code)
	{
		$link = new PartCode();
		$link->code = preg_replace("/[^a-zA-Z0-9]+/","", $code);
		$link->part_id = $product->id;
		$link->old_code = 'yes';
		return $link->save();
	}

}