<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Import;
/**
 * State import completed
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

class ImportCompleteState extends GeneralState {

	/**
	 * Return the form that needs to be processed
	 */
	protected function printFormNew(){

		$str = parent::printFormNew();
		
		$reset_url = $this->getResetUrl();

		$str.=<<<STR
<div class="row bottom-10">
<div class="col-md-12">
	<a class="btn btn-success" href="$reset_url"><span class="glyphicon glyphicon-repeat" ></span> Make a new import</a>
</div>
</div>
STR;


		return $str;
	}
	
	public function printFormOld(){}
	public function processForm(){}
	public function fillFormInput(){}
}
