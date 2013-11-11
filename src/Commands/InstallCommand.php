<?php namespace Jacopo\LaravelImportExport\Commmands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'import-export:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Install Import-Export package.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		// publish config
		$this->call('config:publish', array('package' => 'jacopo/laravel-import-export' ) );
		// publish asset
		$this->call('asset:publish', array('package' => 'jacopo/laravel-import-export' ) );
		// execute migration
		$this->call('migrate', array('package' => 'jacopo/laravel-import-export' ) );
	}

}