<?php namespace Jacopo\LaravelImportExport\Models\StateHandling;

interface StateInterface
{
	/**
	 * Return the form
	 */
	public function getForm();

	/**
	 * Process the form and return the next state
	 * @return  ImportState
	 * @uses getFormInput()
	 * @uses validateFormInput()
	 */
	public function processForm();

	public function getNextState();

	public function fillFormInput();

	public function getErrorHeader();
}