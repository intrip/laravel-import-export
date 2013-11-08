<?php namespace Jacopo\LaravelImportExport\Models\ParseCsv;
/**
 * Builder for csvFile From DB
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class DbFileBuilder
{
	protected $csv_file;
	/**
	 * Import configuration:
	 * 	columns: key (db name) value (export name)
	 *  	table: table name
	 * @var  Array
	 */
	protected $config;

	public function __construct(array $config = null, $csv_file = null)
	{
		$this->config = $config;
		$this->csv_file = $csv_file ? $csv_file : new CsvFile;
	}

	/**
	 * Build the CsvFile
	 * 
	 * @throws  Exception
	 * @throws  PDOException
	 */
	public function build(CsvLine $csv_line_base = null)
	{
		$csv_line_base = $csv_line_base ? $csv_line_base : new CsvLine;
		$stdclass_lines = $this->getAttributesFromDb();
		foreach($stdclass_lines as $key => $line)
		{
			$csv_line = clone $csv_line_base;
			$csv_line->forceSetAttributes((Array)$line);
			$this->appendLine($csv_line);
		}
	}

	/**
	 * Get the data from db
	 * 
	 * @return Array
	 */
	protected function getAttributesFromDb()
	{
		$connection_name = Config::get('LaravelImportExport::baseconf.connection_name');
		return DB::connection($connection_name)
			->table($this->config["table"])
			->select($this->createSelect() )
			->get();
	}

	/**
	 * Create select to make the CsvFile attributes 
	 * to export
	 * 
	 * @return  String
	 */
	protected function createSelect($config = null)
	{
		$config = $config ? $config : $this->config;
		$select = array();
		if( ($columns = $config["columns"]) )
		{
			foreach($columns as $db_key => $export_key)
			{
				$select[]= "{$db_key} as {$export_key}";
			}
		}
		return $select;
	}

	protected function appendLine(CsvLine $line)
	{
		$this->csv_file->append($line);
	}

	public function setConfig($value)
	{
		$this->config = $value;
	}

	public function getCsvFile()
	{
		return $this->csv_file;
	}
}