<?php namespace Jacopo\LaravelImportExport;

use Illuminate\Support\Facades\View;

class ExportController extends BaseController {

	protected $layout = "laravel-import-export::layouts.default";
	protected $menu_index = 3;

	public function getIndex()
	{
		return \App::make('export_csv_handler')->setContent($this->layout);
	}

	public function getResetState()
	{
		\App::make('export_csv_handler')->resetState();

		return \Redirect::action('Jacopo\LaravelImportExport\ExportController@getIndex');
	}

	public function postProcessForm()
	{
		$success =\App::make('export_csv_handler')->processForm();

		return \Redirect::action('Jacopo\LaravelImportExport\ExportController@getIndex'); 
	}

}