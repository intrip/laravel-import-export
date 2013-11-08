<?php 
/**
 * TestGetTableName
 *
 */
use Mockery as m;
use Jacopo\LaravelImportExport\Models\StateHandling\Export\GetTableName;
use Jacopo\LaravelImportExport\Models\Exceptions\NoDataException;

class TestGetTableName extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->state = new GetTableName();
	}

	public function tearDown()
	{
		m::close();
	}

	public function testFillFormInput()
	{
		$input_mock = m::mock('Jacopo\LaravelImportExport\Models\InputWrapper');
		$input_mock->shouldReceive(
   		'get',
   		'get',
   		'get'
   	)->andReturn(
   		'table_name',
   		'50',
   		','
   	);
		$this->state->fillFormInput($input_mock);
		$form_input = $this->state->getFormInput();

		$this->assertEquals('table_name', $form_input["table_name"]);
		$this->assertEquals('50', $form_input["max_rows"]);
		$this->assertEquals(',', $form_input["separator"]);
	}

	public function testGetTableValuesSelect()
	{
		$expected_tables = array(
				"first" => "first",
				"second" => "second",
			);
		$mock_dbm = m::mock('Jacopo\LaravelImportExport\Models\DatabaseSchemaManager');
		$mock_dbm->shouldReceive('getTableList')
		->once()
		->andReturn(array("first","second"));
		$mock_state = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Export\GetTableName')->makePartial();

		$table_list = $mock_state->getTableValuesSelect($mock_dbm);
		$this->assertEquals($expected_tables, $table_list);
	}

	public function testGetTableHeader()
	{
		$mock_cols = m::mock("StdClass")
		->shouldReceive('toArray')
		->andReturn(array("name"=>"name"))
		->getMock();
		$array_mock_cols = array($mock_cols);

		$mock_dbm = m::mock('Jacopo\LaravelImportExport\Models\DatabaseSchemaManager');
		$mock_dbm->shouldReceive('getTableColumns')
		->once()
		->andReturn($array_mock_cols);

		$mock_state = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Export\GetTableName')->makePartial();
		$mock_state->shouldReceive('getFormInput')
		->once()
		->andReturn(array("table_name"=>1));
		$expected_columns = array("name");

		$columns = $mock_state->getTableHeader($mock_dbm);
		$this->assertEquals($expected_columns,$columns);
	}

	public function testProcessFormSuccess()
	{
		$mock_validator = m::mock('Jacopo\LaravelImportExport\Models\ValidatorFormInputModel');
		$mock_validator->shouldReceive('validateInput')
		->once()
		->andReturn(true);
		$this->mockSessionPut();

		$form_input = array(
				'table_name' => 'table',
				'max_rows' => '50',
				'separator' => ','
			);

		$state_stub = new GetTableNameStub;

		$executed = $state_stub->processForm($form_input,$mock_validator);
		$this->assertSame(true,$executed);
	}

	public function testProcessFormFails()
	{
		$mock_validator = m::mock('Jacopo\LaravelImportExport\Models\ValidatorFormInputModel');
		$mock_validator->shouldReceive('validateInput')
		->once()
		->andReturn(false);

		$form_input = array(
				'table_name' => 'table',
				'max_rows' => '50',

			);


		$executed = $this->state->processForm($form_input,$mock_validator);
		$this->assertSame(false,$executed);
	}

	protected function mockSessionPut()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->once()->andReturn($app);

		Illuminate\Support\Facades\Facade::setFacadeApplication($app);
		Illuminate\Support\Facades\Session::swap($input = m::mock('SessionMock'));

		$input->shouldReceive('put')->once()->andReturn(true);
	}

}

class GetTableNameStub extends GetTableName
{
	protected  function getDataTable()
	{
		return true;
	}

	protected function getTableHeader($dbm = null)
	{
		return array("column1");
	}
}
