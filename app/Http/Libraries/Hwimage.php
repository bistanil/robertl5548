<?php

namespace App\Http\Libraries;

use Image;
use Storage;
use App\Models\Watermark;

Class Hwimage {

	public function create($image, $type)
	{	
		$fileName = time().'-'.$image->file('image')->getClientOriginalName();        
        $image->file('image')->move(config('hwimages.'.$type.'.destination'), $fileName);                
        return $fileName;
	}

	public function key($file, $type, $inputName)
	{
		if ($file->hasFile($inputName))
		{	
			$fileName = $file->file($inputName)->getClientOriginalName(); 			
			$file->file($inputName)->move(config('hwimages.'.$type.'.destination'), $fileName);						
	        return $fileName;
	    } else return null;
	}


	public function file($file, $type)
	{
		if ($file->hasFile('docs'))
		{	
			$fileName = time().'-'.$file->file('docs')->getClientOriginalName();        
			$file->file('docs')->move(config('hwimages.'.$type.'.destination'), $fileName);			
	        return $fileName;
	    } else return null;
	}

	public function heighten($image, $type)
	{
		if ($image->hasFile('image'))
		{
			$fileName=$this->create($image, $type);
			$path=config('hwimages.'.$type.'.destination').$fileName;
			$img = Image::make($path)->heighten(config('hwimages.'.$type.'.height'), function ($constraint) {
	                    $constraint->upsize();
	                });
			$watermark=$this->watermark($type);
			if ($watermark!=null) $img->insert(config('hwimages.'.$type.'Watermark.destination').$watermark->image,'center');
	        $img->save($path);
			return $fileName;
		} else return null;
	}

	public function widen($image, $type)
	{
		if ($image->hasFile('image'))
		{
			$fileName=$this->create($image, $type);
			$path=config('hwimages.'.$type.'.destination').$fileName;
			$img = Image::make($path)->widen(config('hwimages.'.$type.'.width'), function ($constraint) {
	                    $constraint->upsize();
	                });
			$watermark=$this->watermark($type);
			if ($watermark!=null) $img->insert(config('hwimages.'.$type.'Watermark.destination').$watermark->image,'center');
	        $img->save($path);
			return $fileName;
		} else return null;
	}

	public function watermark($type)
	{
		$watermarkType=$type.'Watermark';
		return Watermark::where('type',$watermarkType)->first();
	}

	public function destroy($fileName, $type)
	{
		if ($fileName!='')
		{
			$path=config('hwimages.'.$type.'.destination').$fileName;
			if (Storage::has($path)) Storage::delete($path);
			return TRUE;
		} else return null;
	}

}