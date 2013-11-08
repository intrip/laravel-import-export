<?php namespace Jacopo\LaravelImportExport\Models\StateHandling;
/**
 * General state Handler
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\MessageBag;
use Jacopo\LaravelImportExport\Models\Exceptions\NoDataException;
use Jacopo\LaravelImportExport\Models\Exceptions\ClassNotFoundException;

class StateHandler
{
	protected $session_key;
	protected $initial_state;
	protected $state_array;

	/**
	 * Initialize starting import state from session
	 */
	public function initializeState()
	{
		if (Session::has($this->session_key))
		{
			$this->setStateArray( Session::get($this->session_key) );
		}
		else
		{
			$this->resetState();
		}
		return $this->getStateArray();
	}

	/**
	 * Reset the session_array state
	 */
	public function resetState()
	{
		// clean states
		$this->setStateArray( new StateArray() );
		$state_array = $this->getStateArray();
		$state_array->append( new $this->initial_state );
		Session::put($this->session_key,$state_array);
	}

	/**
	 * Fill layout content with forms
	 */
	public function setContent($layout)
	{
		$content = '';

		$state_array = $this->getStateArray();
		foreach($state_array as $state_key => $state)
		{
			$content.=$state->getForm();
		}
		$layout->content = $content;
	}

	/**
	 * Process the current form
	 * @return  $executed if success
	 * @throws  ClassNotFoundException
	 */
	public function processForm()
	{
		$current_state = $this->getCurrent();
		$executed_process = $current_state->processForm();
		// set next state
		$executed_next_state = $this->setNextState($current_state,$executed_process);
		// return success
		return ( $executed_process && $executed_next_state );
	}

	/**
	 * Set the next state
	 *
	 * @return  Boolean $success
	 */
	protected function setNextState($current_state, $executed)
	{
		try
		{
			$next_state = $current_state->getNextState();
		}
		catch(ClassNotFoundException $e)
		{
			Session::put('Errors', new MessageBag( array("Class" => "Class not found") ) );
			return false;
		}

		if($executed)
		{
			// append next state
			$this->append($next_state);
		}
		else
		{
			// replace current state
			$this->replaceCurrent($next_state);
		}

		return true;
	}

	protected function append($value)
	{
		$this->state_array->append($value);
	}

	public function getCurrent()
	{
		return $this->getLastState();
	}

	/**
	 * Replace the current state
	 */
	public function replaceCurrent($value)
	{
		$key = $this->getLastKey();
		if($key >= 0)
		{
			$this->getStateArray()->offsetSet($key,$value);
		}
		else
		{
			throw new NoDataException;
		}
	}

	public function getLastState()
	{
		$key = $this->getLastKey();
		if($key >= 0)
		{
			return $this->getStateArray()[$key];
		}
		else
		{
			throw new NoDataException;
		}
	}

	public function getLastKey()
	{
		return ( count($this->state_array) - 1 );
	}

	public function getStateArray()
	{
		return $this->state_array;
	}

	public function setStateArray($value)
	{
		$this->state_array = $value;
	}

}
