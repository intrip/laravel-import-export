<?php namespace Jacopo\LaravelImportExport\Models;
/**
 * Simple input wrapper
 * 
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\Input;

class InputWrapper
{

	public function get($params, $default_value = null)
	{
		return Input::get($params,$default_value);
	}

	public function post($params, $default_value = null)
	{
		return Input::post($params,$default_value);
	}

	public function all()
	{
		return Input::all();
	}
}