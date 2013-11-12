<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Import;
/**
 * Handler of import state
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Jacopo\LaravelImportExport\Models\StateHandling\StateHandler as StateHandlerBase;

class StateHandler extends StateHandlerBase
{
	protected $session_key;
	protected $initial_state = "Jacopo\LaravelImportExport\Models\StateHandling\Import\GetCsvState";

	public function __construct()
	{
		$this->session_key = Config::get('laravel-import-export::baseconf.session_import_key','import_state');
	}

	/**
	 * Reset the session_array state
	 */
	public function resetState()
	{
		parent::resetState();

		// clean temporary db
		$table_name = Config::get('laravel-import-export::baseconf.table_prefix');
		$connection_name = Config::get('laravel-import-export::baseconf.connection_name');
		DB::connection($connection_name)->table($table_name)->truncate();
	}


}
