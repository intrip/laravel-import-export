<?php namespace Jacopo\LaravelImportExport;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facade\Session;
use Illuminate\Support\Facades\Validator;
use Jacopo\LaravelImportExport\Models\StateHandling\Import\StateHandler as ImportHandler;
use Jacopo\LaravelImportExport\Models\StateHandling\Export\StateHandler as ExportHandler;
use Jacopo\LaravelImportExport\Commmands\InstallCommand;

class LaravelImportExportServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->package('jacopo/laravel-import-export');

		$this->registerImportState();
		$this->registerExportState();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

	public function boot()
	{
		// include start.php
      include_once __DIR__ . '/start.php';

      // Use custom package database configuration
    	$this->app['config']['database.connections'] = array_merge(
	      $this->app['config']['database.connections'],
	      \Config::get('laravel-import-export::database.connections')
    	);
	}

	protected function registerImportState(ImportHandler $handler = null)
	{
	     $this->app['import_csv_handler'] = $this->app->share(function($app)
	        {
	            $handler = new ImportHandler();
	            $handler->initializeState();

	            return $handler;
	        });
	}

	protected function registerExportState(ExportHandler $handler = null)
	{
	     $this->app['export_csv_handler'] = $this->app->share(function($app)
	        {
	            $handler = new ExportHandler();
	            $handler->initializeState();

	            return $handler;
	        });
	}

}