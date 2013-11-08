<?php

use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile;

class TestCsvFile extends PHPUnit_Framework_TestCase {

	protected $csv_file;

	public function setUp()
	{
		$this->csv_file = new CsvFile();
	}

	//@todo write tests
	public function testToMakePass()
	{}
}