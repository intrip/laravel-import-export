<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Export;
/**
 * Handler of export state
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Jacopo\LaravelImportExport\Models\StateHandling\StateHandler as StateHandlerBase;

class StateHandler extends StateHandlerBase
{
	protected $session_key;
	protected $initial_state = "Jacopo\LaravelImportExport\Models\StateHandling\Export\GetTableName";

	public function __construct()
	{
		$this->session_key = Config::get('LaravelImportExport::baseconf.session_export_key','export_state');
	}

}
