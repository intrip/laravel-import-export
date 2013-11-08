<?php

use Jacopo\LaravelImportExport\Models\ParseCsv\ParseCsv;
use Jacopo\LaravelImportExport\Models\ParseCsv\File;

class TestParseCsv extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->parse_csv = new ParseCsv();
	}

	public function tearDown()
	{
		\Mockery::close();
	}

	public function testParseCsvFileSuccess()
	{
		$csv_line_file = array("test"=>"test");
		$mock = \Mockery::mock('Jacopo\LaravelImportExport\Models\ParseCsv\File');
		$mock->shouldReceive(array(
			'openFile'=>true,
			'readCsv'=>$csv_line_file));

		$parse_csv = new ParseCsv(array(),$mock);

		$csv_line_array = $parse_csv->parseCsvFile();
		$this->assertEquals($csv_line_file, $csv_line_array);

		$mock = \Mockery::mock('Jacopo\LaravelImportExport\Models\ParseCsv\File');
		$mock->shouldReceive(array(
			'openFile'=>true,
			'readCsv'=>false,
			'closeFile'=>true));

		$parse_csv = new ParseCsv(array(),$mock);

		$csv_line_array = $parse_csv->parseCsvFile();
		$this->assertEquals(false, $csv_line_array);
	}

}
