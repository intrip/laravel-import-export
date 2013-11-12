<?php namespace Jacopo\LaravelImportExport\Models;
/**
 * Doctrine schema manager wrapper
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class DatabaseSchemaManager
{
	protected $manager;
	/**
	 * Name of the connection to use
	 * @var  String
	 */
	protected $connection;

	public function __construct()
	{
		$this->connection = Config::get('laravel-import-export::baseconf.connection_name');
		$this->manager = DB::connection($this->connection)->getDoctrineSchemaManager();
	}

	public function getTableList()
	{
		$tables = $this->manager->listTableNames();

		return $tables;
	}

	public function getTableColumns($table_name)
	{
		$columns = $this->manager->listTableColumns($table_name);

		return $columns;
	}

	/**
	 * Create a table schme with the given columns
	 * 
	 * @param  $name table name
	 * @param  Array $columns "type" => "name"
	 * @param  $safe_create if is enabled doesnt create a table if already exists
	 * 
	 */
	public function createTable($name, Array $columns, $safe_create = false)
	{
		if( (! $safe_create) || ($safe_create && ! Schema::connection($this->connection)->hasTable('users')) )
		{
			Schema::connection($this->connection)->drop($name);

			Schema::connection($this->connection)->create($name, function($table) use ($columns)
			{
				foreach($columns as $name => $type)
				{
		    		$table->$type($name);
		    	}
			});
		}
	}

}