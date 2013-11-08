<?php

use Mockery as m;
use Jacopo\LaravelImportExport\Models\Exceptions\ClassNotFoundException;
use  Illuminate\Support\MessageBag;

class TestGeneralState extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}

	public function testgetErrorHeaderExists()
	{
		$this->mockSessionMessageBag();
		$error_all_mock = m::mock('StdClass');
		$error_all_mock->shouldReceive('all')
		->once()
		->andReturn(array('Error1'));

		$mock = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\GeneralState')->makePartial();
		$mock->shouldReceive('getErrors')
		->once()
		->andReturn($error_all_mock);

		$header = $mock->getErrorHeader();
		$this->assertSame("<div class=\"alert alert-danger\">Error1</div>".
								"<div class=\"alert alert-danger\">Class not found</div>", $header);
	}

	public function testgetErrorHeaderNotExists()
	{
		$this->mockSessionEmpty();
		$mock = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\GeneralState')->makePartial();
		$mock->shouldReceive('getErrors')
		->once()
		->andReturn(null);

		$header = $mock->getErrorHeader();
		$this->assertSame("", $header);
	}

	public function testGetNextStateExist()
	{
		$mock = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Import\GetCsvState')->makePartial();
		$mock->shouldReceive('getIsExecuted')
		->andReturn(true);

		$next_state = $mock->getNextState();
		$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\StateHandling\Import\GetTableState', $next_state);
	}

	public function testGetNextStateNotExists()
	{
		$mock = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Import\GetCsvState')->makePartial();
		$mock->shouldReceive('getIsExecuted')
		->andReturn(false);

		$next_state = $mock->getNextState();
		$this->assertInstanceOf('Jacopo\LaravelImportExport\Models\StateHandling\Import\GetCsvState', $next_state);
	}

	/**
	 *@expectedException Jacopo\LaravelImportExport\Models\Exceptions\ClassNotFoundException
	 */
	public function testGetNextStateThrowsClassNotFoundException()
	{
		$mock = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Import\GetCsvState')->makePartial();
		$mock->shouldReceive(array(
			'getIsExecuted' => true,
			'getNextStateClass' => 'invalid__class___name____'
			));

		$next_state = $mock->getNextState();
	}

	public function testAppendErrorAppend()
	{
		$csv_state = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\GeneralState')->makePartial();

		$error_key = "Error1";
		$error_string = "1";

		$csv_state->appendError($error_key, $error_string);

		$this->assertInstanceOf( 'Illuminate\Support\MessageBag' , $csv_state->getErrors() );
		$this->assertSame( array("1"), $csv_state->getErrors()->all() );
	}

	public function testAppendErrorCreateNew()
	{
		$csv_state = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\GeneralState')->makePartial();

		$error = array("Error1" => "1");
		$csv_state->setErrors(new MessageBag($error));
		$csv_state->appendError("Error2", "1");

		$this->assertInstanceOf( 'Illuminate\Support\MessageBag' , $csv_state->getErrors() );
		$this->assertSame( array("1","1"), $csv_state->getErrors()->all() );
	}

	protected function mockSessionMessageBag()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->once()->andReturn($app);

		Illuminate\Support\Facades\Facade::setFacadeApplication($app);
		Illuminate\Support\Facades\Session::swap($session = m::mock('SessionMock'));

		$session->shouldReceive('get','forget')
			->andReturn(new MessageBag( array("Class" => "Class not found") ), true );
	}

	protected function mockSessionEmpty()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->once()->andReturn($app);

		Illuminate\Support\Facades\Facade::setFacadeApplication($app);
		Illuminate\Support\Facades\Session::swap($session = m::mock('SessionMock'));

		$session->shouldReceive('get','forget')
			->andReturn(false,true);
	}
}