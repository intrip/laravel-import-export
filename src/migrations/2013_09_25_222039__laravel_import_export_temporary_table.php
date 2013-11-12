<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class LaravelImportExportTemporaryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$table_name = $this->getTableName();
		Schema::create($table_name, function($table)
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
		$table_name = $this->getTableName();

		Schema::drop($table_name);
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