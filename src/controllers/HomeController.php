<?php namespace Jacopo\LaravelImportExport;

use Illuminate\Support\Facades\View;

class HomeController extends BaseController {

	protected $layout = "laravel-import-export::layouts.default";
	protected $menu_index = 1;

	public function getIndex()
	{
		 $this->layout->nest('content','laravel-import-export::home/index');
	}

}