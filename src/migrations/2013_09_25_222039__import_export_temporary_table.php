<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class ImportExportTemporaryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$connection_name = $this->getConnectionName();
		$table_name = $this->getTableName();

		Schema::connection($connection_name)->create($table_name, function($table)
		{
			$table->increments('id');
		});
		DB::statement('ALTER TABLE  `'.$table_name.'` ADD  `file_object` LONGBLOB NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$connection_name = $this->getConnectionName();
		$table_name = $this->getTableName();

		Schema::connection($connection_name)->drop($table_name);
	}

	private function getTableName()
	{
		return Config::get('LaravelImportExport::baseconf.table_prefix');
	}

	private function getConnectionName()
	{
		return Config::get('LaravelImportExport::baseconf.connection_name');
	}

}