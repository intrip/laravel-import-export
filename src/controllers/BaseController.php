<?php namespace Jacopo\LaravelImportExport;

use Controller;
use Illuminate\Support\Facades\View;

class BaseController extends Controller {
	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	
	/**
	 * index of the menu to activate
	 * @var integer
	 */
	protected $menu_index = 1;

	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
			// set menu index
			$this->layout->menu_index = $this->menu_index;
		}
	}
}