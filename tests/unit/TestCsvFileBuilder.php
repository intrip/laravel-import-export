<?php

use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileBuilder;
use Jacopo\LaravelImportExport\Models\ParseCsv\ParseCsv;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvLine;
use Mockery as m;

class TestCsvFileBuilder extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		\Mockery::close();
	}

	public function testBuildCreateFileFromCsvSuccessNoHeaders()
	{
		$mocked_csv_data = array(
				array("bohemian","rhapsody"),
				array("super","nintendo"),
				array("super","mario"),
				false,
			);
		$mock = \Mockery::mock('Jacopo\LaravelImportExport\Models\ParseCsv\ParseCsv')->makePartial();
		$mock->shouldReceive('parseCsvFile')
			->times(4)
			->andReturn($mocked_csv_data[0],$mocked_csv_data[1],$mocked_csv_data[2],$mocked_csv_data[3]);
		$this->mockConfig();

		$builder = new CsvFileBuilder(array(),array(),array("first_line_headers"=>false),null,$mock);
		$builder->buildFromCsv();

		$csv_file = $builder->getCsvFile();

		$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile', $csv_file);

		// check headers
		$this->assertEmpty($csv_file->getCsvHeader());

		// check data
		foreach($csv_file as $key_csv_line => $csv_line)
		{
			$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\ParseCsv\CsvLine', $csv_line);
			$this->assertSame($mocked_csv_data[$key_csv_line], $csv_line->getAttributes());
		}
	}

	public function testBuildCreateFileFromCsvSuccessWithHeaders()
	{
		$mocked_csv_data = array(
				array("name","surname"),
				array("Jacopo","Beschi"),
				false,
			);
		$mock = \Mockery::mock('Jacopo\LaravelImportExport\Models\ParseCsv\ParseCsv')->makePartial();
		$mock->shouldReceive('parseCsvFile')
			->times(3)
			->andReturn($mocked_csv_data[0],$mocked_csv_data[1],$mocked_csv_data[2]);

		$builder = new CsvFileBuilder(array(),array(),array("first_line_headers"=>true),null,$mock);
		$builder->buildFromCsv();

		$csv_file = $builder->getCsvFile();

		$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile', $csv_file);

		// check headers
		$csv_file->rewind();
		$this->assertSame($mocked_csv_data[0],$csv_file->getCsvHeader());

		// check values
		$second = $csv_file->current();
		$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\ParseCsv\CsvLine', $second);
		$this->assertSame($mocked_csv_data[1], $second->getAttributes());
	}

	protected function mockConfig()
	{
	  $app = m::mock('AppMock');
     $app->shouldReceive('instance')->once()->andReturn($app);

     Illuminate\Support\Facades\Facade::setFacadeApplication($app);
     Illuminate\Support\Facades\Config::swap($config = m::mock('ConfigMock'));

     $config->shouldReceive('get')
     ->andReturn(true);
	}

}