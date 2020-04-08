<?php

function delete_form($routeParams, $label='Delete')
{
	$form = Form::open(['method' => 'DELETE', 'route' => $routeParams], ['name'=>'MyForm']);
	$form .= '<button type="submit" class="btn-options" id="deleteConfirmation"><i class="icon-bin"></i>'.$label.'</button>';
	$form .= Form::close();
	return $form;
}

function flash($title = null, $message = null)
{
	$flash = app('App\Http\Libraries\Flash');

	if (func_num_args() == 0)
	{
		return $flash;
	}

	return $flash->info($title, $message);

}

function dropdownOptions($list, $items = null)
{
	$options=app('App\Http\Libraries\DropdownOptions');
	return $options->$list($items);
}

function hwImage($request = null, $type = null)
{		
	$image = app('App\Http\Libraries\Hwimage');		
	return $image;
}

function prepCode($code)
{
	$code=str_replace(' ', '', $code);
    $code=str_replace('.', '', $code);
    $code=str_replace('/', '', $code);
    $code=str_replace('-', '', $code);
    return $code;
}
