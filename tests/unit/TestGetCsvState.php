<?php

use Jacopo\LaravelImportExport\Models\StateHandling\Import\GetCsvState;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile;
use Mockery as m;

class TestGetCsvState extends PHPUnit_Framework_TestCase {

	protected $state;

	public function setUp()
	{
		$this->state = new GetCsvState;
	}

	public function tearDown()
	{
		m::close();
	}

	public function testProcessFormSuccess()
	{
		$mock_validator = m::mock('Jacopo\LaravelImportExport\Models\ValidatorFormInputModel');
		$mock_validator->shouldReceive('validateInput')
		->once()
		->andReturn(true);
		$this->mockSessionPut();
		
		$mock_file = m::mock('StdClass');
		$mock_file->shouldReceive(array(
			'getRealPath' => 'path/path',
			'getClientOriginalName' => 'test.csv',
			));

			$form_input = array(
					'separator' => ';',
					'file_csv' => $mock_file,
					'headers' => false,
					'max_lines' => 1,
					'create_table'  => true,
				);

		$mock_csv_handler = m::mock('Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileHandler');
		$mock_csv_handler->shouldReceive(array(
			'openFromCsv' => true,
			'saveTemporary' => true,
		));

		$executed = $this->state->processForm($form_input,$mock_validator,$mock_csv_handler);
		$this->assertSame(true,$executed);
	}

	public function testProcessFormFailsValidation()
	{
		$mock_file = m::mock('StdClass');
		$mock_file->shouldReceive(array(
			'getRealPath' => 'path/path',
			'getClientOriginalName' => 'test.csv',
		));

		$form_input = array(
				'separator' => ';',
				'file_csv' => $mock_file,
				'headers' => false,
		);

		$mock_temporary_model = m::mock('Jacopo\LaravelImportExport\Models\TemporaryModel');

		$mock_validator = m::mock('Jacopo\LaravelImportExport\Models\ValidatorFormInputModel');
		$mock_validator->shouldReceive('validateInput')
		->once()
		->andReturn(false);

		$executed = $this->state->processForm($form_input,$mock_validator,null);
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