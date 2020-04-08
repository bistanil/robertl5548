<?php

namespace App\Http\Libraries;

Class FrontFlash {

	public function create($title, $message, $level, $key='frontFlashMessage')
	{
		return session()->flash($key, [
				'title' => $title,
				'message' => $message,
				'level' => $level
			]);
	}

	public function info($title, $message)
	{
		return $this->create($title, $message, 'info');
	}

	public function success($title, $message)
	{
		return $this->create($title, $message, 'success');
	}

	public function error($title, $message)
	{
		return $this->create($title, $message, 'danger');
	}

	public function overlay($title, $message, $level='success')
	{
		return $this->create($title, $message, $level, 'flash_message_overlay');
	}

}