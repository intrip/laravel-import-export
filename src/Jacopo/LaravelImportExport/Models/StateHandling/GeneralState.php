<?php namespace Jacopo\LaravelImportExport\Models\StateHandling;
/**
 * Basic state
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Jacopo\LaravelImportExport\Models\StateHandling\StateInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Jacopo\LaravelImportExport\Models\Exceptions\ClassNotFoundException;

abstract class GeneralState implements StateInterface {

	/**
	 * Name of the next state class
	 * @var String
	 */
	protected $next_state_class;
	/**
	 * Name of the action to process the form
	 * @var String
	 */
 	protected $process_action;
	/**
	 * if the status has already been executed with success
	 * @var boolean
	 */
	/**
	 * Name of the action to reset current state
	 * @var string
	 */
 	protected $reset_state;
 	/**
 	 * If the state has been executed
 	 * @var boolean
 	 */
	protected $is_executed = false;
	/**
	 * The imput given from the form
	 * @var Mixed
	 */
	protected $form_input;
	/**
	 * Validation rules
	 * @var Array
	 */
	protected $rules;
	/**
	 * Validation erorrs
	 * @var Array
	 */
	protected $errors;
	/**
	 * key of the session used to exchange data between states
	 * @var string
	 */
	protected $exchange_key;
	/**
	 * Return the form
	 */
	/**
	 * Name of session key which store errors
	 * @var String
	 */
	protected $errors_key;

	public function getForm(){

		if( ! $this->getIsExecuted() )
		{
			return $this->printFormNew();
		}
		else
		{
			return $this->printFormOld();
		}
	}

	protected function getProcessUrl()
	{
		return URL::action($this->process_action);
	}

	/**
	 * Return the next state
	 *
	 * @throws Jacopo\LaravelImportExport\Exception\ClassNotFoundException
	 * @return ImportState
	 */
	public function getNextState()
	{
		if( $this->getIsExecuted() )
			{
				if ( class_exists( $next = $this->getNextStateClass() ) )
				{
					return (new $next );
				}
				else
				{
					throw new ClassNotFoundException;
				}
			}
			else
			{
				return $this;
			}
	}

	/**
	 * Print the form errors if exists
	 * 
	 * @return String|null
	 */
	public function getErrorHeader()
	{
		$err_str = '';
		$error_class = $this->getErrors();
		$error_session = Session::get($this->errors_key);

		if( $error_class )
		{
			foreach ($error_class->all() as $error_key => $error) {
				$err_str.="<div class=\"alert alert-danger\">{$error}</div>";
			}
		}

		if( $error_session)
		{
			foreach ($error_session->all() as $error_key => $error) {
				$err_str.="<div class=\"alert alert-danger\">{$error}</div>";
			}
		}

		$this->resetErrors();

		return $err_str;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function setErrors($value)
	{
		$this->errors = $value;
	}

	public function getFormInput()
	{
		return $this->form_input;
	}

	public function getIsExecuted()
	{
		return $this->is_executed;
	}

	public function getNextStateClass()
	{
		return $this->next_state_class;
	}

	/**
	 * Return the form that needs to be processed
	 */
	protected function printFormNew()
	{
		return $this->getErrorHeader();
	}
	/**
	 * Return the form that has already been processed
	 */
	protected abstract function printFormOld();

	/**
	 * Process the form and update the current state
	 * @return  ImportState
	 */
	public abstract function processForm();

	public abstract function fillFormInput();

	function __sleep()
	{
		$serialize_fields = array();

		$serialize_fields[] = 'next_state_class';
		$serialize_fields[] = 'is_executed';
		$serialize_fields[] = 'process_action';
		$serialize_fields[] = 'reset_state';
		$serialize_fields[] = 'rules';
		$serialize_fields[] = 'errors';

		return $serialize_fields;
	}

	/**
	 * Append error to the MessageBag
	 */
	public function appendError($error_key, $error_string)
	{
		if($this->getErrors())
		{
			// append
			$this->errors->add( $error_key, $error_string );
		}
		else
		{
			// new MessageBag
			$this->errors = new MessageBag( array($error_key => $error_string) );
		}
	}

	protected function getResetUrl()
	{
		return \URL::action($this->reset_state);
	}

	/**
	 * Clean all errors
	 */
	protected function resetErrors()
	{
		Session::forget($this->errors_key);
		$this->errors = array();
	}

}
