<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Import;
/**
 * Basic import state
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Jacopo\LaravelImportExport\Models\StateHandling\GeneralState as GeneralStateBase;

abstract class GeneralState extends GeneralStateBase {

 	protected $process_action = "Jacopo\LaravelImportExport\ImportController@postProcessForm";
 	protected $reset_state = "Jacopo\LaravelImportExport\ImportController@getResetState";
	protected $exchange_key = 'import_state_exchange';
	protected $errors_key = 'errors_import';
	
}
