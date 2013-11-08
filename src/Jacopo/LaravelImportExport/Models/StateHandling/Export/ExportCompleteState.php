<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Export;
/**
 * State export completed
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;

class ExportCompleteState extends GeneralState {

	/**
	 * Return the form that needs to be processed
	 */
	protected function printFormNew(){
		$str = parent::printFormNew();
		
		$reset_url = $this->getResetUrl();
		$download_path = Session::get($this->exchange_key);

		$str.=<<<STR
<div class="row bottom-10">
<div class="col-md-12">
	<a class="btn btn-success" href="/{$download_path}"><span class="glyphicon glyphicon-download" ></span> Download File</a>
	<a class="btn btn-success" href="{$reset_url}"><span class="glyphicon glyphicon-repeat" ></span> Make a new Export</a>
</div>
</div>
STR;


		return $str;
	}
	
	public function printFormOld(){}
	public function processForm(){}
	public function fillFormInput(){}
}
