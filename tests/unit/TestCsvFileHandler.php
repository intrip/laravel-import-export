<?php

use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileHandler;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileBuilder;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvLine;
use Mockery as m;

class TestCsvFileHandler extends PHPUnit_Framework_TestCase {

	protected $csv_file_handler;

	public function setUp()
	{
		$this->csv_file_handler = m::mock('Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileHandler')->makePartial();
	}

	public function tearDown()
	{
		m::close();
	}

	public function testOpenFromCsvSuccess()
	{
		$mock = m::mock('Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileBuilder')->makePartial();
		$mock->shouldReceive('buildFromCsv','getCsvFile')
		->andReturn(true,true);

		$config = array(
			"model" => 1,
			"file_parse"  => 2,
			"builder" => 3,
			);

		$result = $this->csv_file_handler->openFromCsv($config,$mock);

		$this->assertSame(true,$result);
	}

	public function testSaveTemporarySuccess()
	{
		$this->mockConfig();
		$this->mockDbConnection();
		$temporary_stub = new TemporaryModelStub();
		$csv_file = new CsvFile();

		$success = $this->csv_file_handler->saveTemporary($csv_file,$temporary_stub);

		$this->assertTrue($success);
		$this->assertTrue($_SERVER['__temporary.saved']);
	}

	public function testGetMaxLength()
	{
		$csv_line1 = new CsvLine();
		$csv_line1->first = 1;
		$csv_line1->second = 2;
		$csv_line2 = new CsvLine();

		$csv_file = new CsvFile(array($csv_line1, $csv_line2));

		$expected_length = 2;
		$length = $this->csv_file_handler->getMaxLength($csv_file);

		$this->assertEquals($expected_length, $length);
	}

	/**
	 * @expectedException Jacopo\LaravelImportExport\Models\Exceptions\NoDataException
	 */
	public function testGetMaxLengthThrowsNoDataException()
	{
		$this->csv_file_handler->getMaxLength();
	}

	/**
	 * @expectedException Jacopo\LaravelImportExport\Models\Exceptions\UnalignedArrayException
	 */
	public function testUpdateHeadersThrowsUnalignedArrayException()
	{
		$line1 = new CsvLine();
		$line1->setAttribute("0", "first");
		$line1->setAttribute("1", "second");
		$line2 = new CsvLine();
		$line2->setAttribute("0", "first");
		$csv_file = new CsvFile( array($line1, $line2) );
		
		$columns = array(
			"first" => 0,
			"second" => 1
			);

		$table_name = "table";

		$this->csv_file_handler->updateHeaders($csv_file, $columns, $table_name);
	}

	public function testUpdateHeadersSuccess()
	{
		$line1 = new CsvLine();
		$line1->setAttribute("0", "first");
		$line1->setAttribute("1", "second");
		$line2 = new CsvLine();
		$line2->setAttribute("0", "first");
		$line2->setAttribute("1", "second");
		$csv_file = new CsvFile( array($line1, $line2) );
		
		$columns = array(
				 0 => "first",
				 1 => "second",
			);

		$table_name = "table";

		$this->csv_file_handler->updateHeaders($csv_file, $columns, $table_name);
		$csv_file_updated = $this->csv_file_handler->getCsvFile();

		$line_1_expected = new CsvLine();
		$line_1_expected->setAttribute("first", "first");
		$line_1_expected->setAttribute("second", "second");
		$line_1_expected->setConfig( array("table" => "table") );
		$line_2_expected = new CsvLine();
		$line_2_expected->setAttribute("first", "first");
		$line_2_expected->setAttribute("second", "second");
		$line_2_expected->setConfig( array("table" => "table") );
		$csv_file_expected = new CsvFile( array($line_1_expected, $line_2_expected) );

		$this->assertEquals($csv_file_expected, $csv_file_updated);
	}

	public function testGetCsvStringSuccess()
	{
		$mock_line = m::mock('CsvLine')->makePartial();
		$mock_line->shouldReceive(array(
				'getCsvHeader' => "header\n",
				'getCsvString' => "string\n"
			));

		$csv_file = new CsvFile();
		$csv_file->append($mock_line);

		$expected_str="header\nstring\n";
		$csv_str = $this->csv_file_handler->getCsvString(',', $csv_file);

		$this->assertEquals($expected_str, $csv_str);
	}

	/**
	 * @expectedException Jacopo\LaravelImportExport\Models\Exceptions\NoDataException
	 */
	public function testGetCsvStringThrowsNoDataException()
	{
		$this->csv_file_handler->getCsvString(",");
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

	protected function mockDbConnection()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->andReturn($app);

   	Illuminate\Support\Facades\Facade::setFacadeApplication($app);
   	Illuminate\Support\Facades\DB::swap($db = m::mock('DBMock'));

   	$mock_connection = m::mock("StdClass");
   	$mock_connection->shouldReceive('disableQueryLog');

   	$db->shouldReceive('connection')
   	->andReturn($mock_connection);
	}
	
}

class TemporaryModelStub extends Illuminate\Database\Eloquent\Model {
	protected static $unguarded = true;

	public function save(array $options = array()) 
	{
		$_SERVER['__temporary.saved'] = true;

		return true;
	}
}