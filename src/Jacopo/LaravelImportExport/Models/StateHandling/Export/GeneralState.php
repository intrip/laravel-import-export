<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Export;
/**
 * Basic export state
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Jacopo\LaravelImportExport\Models\StateHandling\GeneralState as GeneralStateBase;

abstract class GeneralState extends GeneralStateBase {

 	protected $process_action = "Jacopo\LaravelImportExport\ExportController@postProcessForm";
 	protected $reset_state = "Jacopo\LaravelImportExport\ExportController@getResetState";
	protected $exchange_key = 'export_state_exchange';
	protected $errors_key = 'errors_export';
}
