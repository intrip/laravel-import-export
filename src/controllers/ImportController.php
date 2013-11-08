<?php namespace Jacopo\LaravelImportExport;

use Illuminate\Support\Facades\View;

class ImportController extends BaseController {

	protected $layout = "LaravelImportExport::layouts.default";
	protected $menu_index = 2;

	public function getIndex()
	{
		\App::make('import_csv_handler')->setContent($this->layout);
	}

	public function getResetState()
	{
		\App::make('import_csv_handler')->resetState();

		return \Redirect::action('Jacopo\LaravelImportExport\ImportController@getIndex');
	}

	public function postProcessForm()
	{
		$success =\App::make('import_csv_handler')->processForm();

		return \Redirect::action('Jacopo\LaravelImportExport\ImportController@getIndex'); 
	}
}