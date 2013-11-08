<?php 
/**
 * TestGetTableConfig
 *
 */
use Mockery as m;
use Jacopo\LaravelImportExport\Models\StateHandling\Export\GetTableConfig;


class TestGetTableConfig extends \PHPUnit_Framework_TestCase
{
	protected $state;

	public function setUp()
	{
		$this->state = new GetTableConfig;
	}

	public function tearDown()
	{
		m::close();
	}

	public function testProcessFormSuccess()
	{
		$form_input = array(
				"attributes" => '',
				'table_name' => ''
			);
		$mock_handler = m::mock('Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileHandler')
			->shouldReceive(array(
					'openFromDb' => true,
					"saveCsvFile" => true,
				))
			->getMock();
		$mock_validator = m::mock('StdClass')
			->shouldReceive('validateInput')
			->andReturn(true)
			->getMock();

		$this->mockSessionGetPut();
		$success = $this->state->processForm($form_input, $mock_validator, $mock_handler);

		$this->assertTrue($success);
	}

	public function testProcessFormFailsValidation()
	{
		$form_input = array(
				"attributes" => '',
				'table_name' => ''
			);
		$mock_validator = m::mock('StdClass')
			->shouldReceive('validateInput')
			->andReturn(true)
			->getMock();

		$success = $this->state->processForm($form_input, $mock_validator);

		$this->assertFalse($success);
	}

	protected function mockSessionGetPut()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->once()->andReturn($app);

		Illuminate\Support\Facades\Facade::setFacadeApplication($app);
		Illuminate\Support\Facades\Session::swap($session = m::mock('ConfigMock'));

		$session->shouldReceive('get','put');
	}
}

