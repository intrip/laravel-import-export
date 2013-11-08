<?php namespace Jacopo\LaravelImportExport\Models;
/**
 * Validate form input in the model
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;

class ValidatorFormInputModel extends Model {

	protected $model;

	public function __construct($model)
	{
		$this->model = $model;
	}

	/**
	 * Validate form_input with $rules
	 * @return Boolean
	 */
	public function validateInput()
	{
		$v = Validator::make ( $this->model->getFormInput() , $this->model->getRules() );

		$success = true;
		if ( $v->fails() )
		{
			$this->model->setErrors( $v->messages() );
			$success = false;
		}

		return $success;
	}

}
