<?php

namespace App\Http\Libraries;

use App\Models\CarPart;
use App\Models\ModelPart;
use App\Models\TypePart;
use DB;

Class CarConnections{

	public function __construct()
	{

	}

	public function create($partId, $request)
	{
		$this->createCarConnections($partId, $request->cars);
		$this->createModelConnections($partId, $request->models);
		$this->createTypeConnections($partId, $request->types);
	}

	public function destroy($partId)
	{
		$this->destroyCarConnections($partId);
		$this->destroyModelConnections($partId);
		$this->destroyTypeConnetions($partId);
	}

	public function createCarConnections($partId, $cars)
	{
		$this->destroyCarConnections($partId);
		foreach ($cars as $key => $carId) {
			$connection = new CarPart();
			$connection->part_id = $partId;
			$connection->car_id = $carId;
			$connection->save();
		}
	}

	public function destroyCarConnections($partId)
	{
		if (CarPart::where('part_id', '=', $partId)->delete()) return TRUE;
		else return FALSE;
	}

	public function createModelConnections($partId, $models)
	{
		$this->destroyModelConnections($partId);
		foreach ($models as $key => $modelId) {
			$connection = new ModelPart();
			$connection->part_id = $partId;
			$connection->model_id = $modelId;
			$connection->save();
		}
	}

	public function destroyModelConnections($partId)
	{
		if (ModelPart::where('part_id', '=', $partId)->delete()) return TRUE;
		else return FALSE;
	}

	public function createTypeConnections($partId, $types)
	{
		$this->destroyTypeConnetions($partId);
		foreach ($types as $key => $typeId) {
			DB::table('type_parts')->insert(['type_id' => $typeId, 'part_id' => $partId]);
		}
	}

	public function destroyTypeConnetions($partId)
	{
		if (TypePart::where('part_id', '=', $partId)->delete()) return TRUE;
		else return FALSE;
	}

	public function CarsConnections($partId)
	{
		return CarPart::wherePart_id($partId)->pluck('car_id')->toArray();
	}

	public function ModelsConnections($partId)
	{
		return ModelPart::wherePart_id($partId)->pluck('model_id')->toArray();
	}

	public function TypesConnections($partId)
	{
		return TypePart::wherePart_id($partId)->pluck('type_id')->toArray();
	}

}