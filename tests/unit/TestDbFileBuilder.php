<?php 
/**
 * TestDbFileBuilder
 *
 */
use Mockery as m;
use Jacopo\LaravelImportExport\Models\ParseCsv\DbFileBuilder;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvLine;

class TestDbFileBuilder extends \PHPUnit_Framework_TestCase
{

	protected $builder;

	public function setUp()
	{
		$this->builder = m::mock("Jacopo\LaravelImportExport\Models\ParseCsv\DbFileBuilder")->makePartial();
	}

	public function testCreateSelect()
	{
		$config = array(
				"columns" => array(
						"db1" => "csv1",
						"db2" => "csv2"
					)
			);
		$select = $this->builder->createSelect($config);
		$expected_select = array(
				"db1 as csv1",
				"db2 as csv2",
			);
		$this->assertEquals($select,$expected_select);
	}

	public function testBuildSuccess()
	{
		$builder = new DbBuilderStub();
		$this->mockConfig();

		$expected_line = new CsvLine();
		$expected_line->forceSetAttributes(array("csv1"=>"data"));

		$builder->build();
		$csv_file = $builder->getCsvFile();

		$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile', $csv_file);
		foreach($csv_file as $csv_line)
		{
			$this->assertEquals($expected_line->getAttributes(), $csv_line->getAttributes());
		}
	}

	protected function mockConfig()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->andReturn($app);

   	Illuminate\Support\Facades\Facade::setFacadeApplication($app);
   	Illuminate\Support\Facades\Config::swap($config = m::mock('ConfigMock'));

   	$config->shouldReceive('get')
   	->andReturn('import');
	}
}

class DbBuilderStub extends DbFileBuilder
{
	protected function getAttributesFromDb()
	{
		return array( array("csv1" => "data") );
	}
}