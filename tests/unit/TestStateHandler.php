<?php

use Jacopo\LaravelImportExport\Models\StateHandling\StateHandler;
use Jacopo\LaravelImportExport\Models\StateHandling\StateArray;
use Jacopo\LaravelImportExport\Models\StateHandling\Import\GetCsvState;
use Illuminate\Support\Facades\Session;
use Mockery as m;

class TestStateHandler extends PHPUnit_Framework_TestCase {

	protected $handler;
	protected $session_key = 'import_state';

	public function tearDown()
	{
		m::close();
	}

	public function testInitializeStateSuccessExists()
	{
		$this->handler = new StateHandler();
		$this->mockSessionExist();

		$this->handler->initializeState();
		$current_state = Session::get($this->session_key);
		$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\StateHandling\StateArray', $current_state); 
	}

	public function testInizializeStateSuccessReset()
	{
		$mock = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\StateHandler')->makePartial();
		$mock->shouldReceive('resetState')
		->once()
		->andReturn(true);

		$this->mockSessionReset();
		$mock->initializeState();
	}

	public function testResetStateSuccess()
	{
		$handler_stub = new StateHandlerStubReset;

		$this->mockSessionReset();
		$this->mockConfigTableDb();

		$handler_stub->resetState();

		$returned_array_state = $handler_stub->getStateArray();
		$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\StateHandling\StateArray', $returned_array_state);
		$current_state = $handler_stub->getCurrent();
		$this->assertInstanceOf('StdClass', $current_state);

	}

	public function testReplaceCurrentSuccess()
	{
		$mock_state_array = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\StateArray');
		$mock_state_array->shouldReceive('offsetSet')
		->once()
		->andReturn(true);

		$mock_handler = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\StateHandler')->makePartial();
		$mock_handler->shouldReceive(array(
				"getLastKey" => 0,
				"getStateArray" => $mock_state_array,
			));

		$mock_handler->replaceCurrent( array() );
	}

	/**
	 *@expectedException Jacopo\LaravelImportExport\Models\Exceptions\NoDataException
	 */
	public function testReplaceCurrentThrowsNoDataException()
	{
		$mock_handler = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\StateHandler')->makePartial();
		$mock_handler->shouldReceive('getLastKey')
		->once()
		->andReturn(-1);
		$mock_handler->replaceCurrent( array() );
	}

	public function testProcessFormSuccess()
	{
		$handler = new StateHandler();
		$mock_get_csv_s = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\GeneralState')->makePartial();
		$mock_get_csv_s->shouldReceive('processForm')
		->once()
		->andReturn(true);
		$this->mockSessionPut();

		$handler->setStateArray( new StateArray( array( $mock_get_csv_s ) ) );

		$handler->processForm();
	}

	public function textGetLastStateThrowsNoDataException()
	{
		$mock_handler = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Import\StateHandler')->makePartial();
		$mock_handler->shouldReceive('getLastKey')
		->once()
		->andReturn(-1);

		$mock_handler->getLastState();
	}

	public function textGetLastStateSuccess()
	{
		$state_array = array("test array");

		$mock_handler = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Import\StateHandler')->makePartial();
		$mock_handler->shouldReceive(array(
				"getLastKey" => 0,
				"getStateArray" => $state_array,
			));

		$res = $mock_handler->getLastState();
		$this->assertEquals($state_array, $mock_handler->getStateArray());
	}

	protected function mockSessionExist()
	{
   	Illuminate\Support\Facades\Session::swap($session = m::mock('SessionMock'));
   	$session->shouldReceive(array(
	   		'has'=>true,
	   		'get' => new StateArray()
   		));
	}

	protected function mockSessionReset()
	{
   	Illuminate\Support\Facades\Session::swap($session = m::mock('SessionMock'));
   	$session->shouldReceive(array(
	   		'has'=>false,
	   		'put'=>true,
   		));
	}

	protected function mockSessionPut()
	{
		Illuminate\Support\Facades\Session::swap($session = m::mock('SessionMock'));
   	$session->shouldReceive(array(
	   		'put'=> true
   		));
	}

	protected function mockDbTruncate()
	{
		$mock_truncate = m::mock("StdClass")
		->shouldReceive("truncate")
		->once()
		->andReturn(true)
		->getMock();

		$mock_table = m::mock('StdClass')
		->shouldReceive('table')
		->andReturn($mock_truncate)
		->getMock();

		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->andReturn($app);

   	Illuminate\Support\Facades\Facade::setFacadeApplication($app);
		Illuminate\Support\Facades\DB::swap($db = m::mock('DBMock'));
   	$db->shouldReceive(array(
	   		'connection'=> $mock_table
   		));
	}

	protected function mockConfigTable()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->andReturn($app);

   	Illuminate\Support\Facades\Facade::setFacadeApplication($app);
   	Illuminate\Support\Facades\Config::swap($config = m::mock('ConfigMock'));

   	$config->shouldReceive('get')->once()->with('LaravelImportExport::baseconf.table_prefix')->andReturn(true);
	}	

	protected function mockConfigTableDb()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->andReturn($app);

   	Illuminate\Support\Facades\Facade::setFacadeApplication($app);
   	Illuminate\Support\Facades\Config::swap($config = m::mock('ConfigMock'));

   	$config->shouldReceive('get','get')
   	->andReturn(true,'import');
	}

	protected function mockConfig()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->andReturn($app);

   	Illuminate\Support\Facades\Facade::setFacadeApplication($app);
   	Illuminate\Support\Facades\Config::swap($config = m::mock('ConfigMock'));

   	$config->shouldReceive('get')->once()->with('LaravelImportExport::baseconf.session_import_key','import_state')->andReturn($this->session_key);
	}

}

class StateHandlerStubReset extends StateHandler
{
	protected $initial_state = 'StdClass';
}