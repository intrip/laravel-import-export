<?php

use Jacopo\LaravelImportExport\Models\ParseCsv\CsvLine;

class TestCsvLine extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->csv_line = new CsvLine();
	}

	public function testgetElementsSuccess()
	{
		$this->csv_line->name = "name";
		$attrs = $this->csv_line->getElements();
		$expected = array("name"=>"name");

		$this->assertEquals($expected,$attrs);
	}

	public function testgetCsvHeader()
	{
		$headers = array(
				"first" => 1,
				"second" => 2
			);
		$this->csv_line->forceSetAttributes($headers);
		
		$expected_header = "first,second\n";
		$csv_header = $this->csv_line->getCsvHeader(",");

		$this->assertEquals($expected_header,$csv_header);
	}

	public function testgetCsvString()
	{
		$headers = array(
				"first" => 1,
				"second" => 2
			);
		$this->csv_line->forceSetAttributes($headers);
		
		$expected_string = "1,2\n";
		$csv_string = $this->csv_line->getCsvString(",");

		$this->assertEquals($expected_string, $csv_string);
	}
}