<?php

use Jacopo\LaravelImportExport\Models\ValidatorFormInputModel;
use Mockery as m;

class TestValidatorFormInputModel extends PHPUnit_Framework_TestCase {

	protected $import_state_mock;

	public function setUp()
	{
		// create import state mock for both cases
		$uploaded_file_mock = m::mock('Symfony\Component\HttpFoundation\File\UploadedFile');
		$rules = array();
		$import_state_mock = m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Import\ImportState');
		$import_state_mock->shouldReceive(array(
				'getFormInput' => $uploaded_file_mock,
				'getRules' => $rules,
				'setErrors' => true,
			));
		$this->import_state_mock = $import_state_mock;
	}

	public function tearDown()
	{
		m::close();
	}

	public function testValidateInputSuccess()
	{
		$this->mockValidatorSuccess();
		$validator = new ValidatorFormInputModel($this->import_state_mock);
		$success = $validator->validateInput();

		$this->assertTrue($success);
	}

	public function testValidateInputFail()
	{
		$this->mockValidatorFail();
		$validator = new ValidatorFormInputModel($this->import_state_mock);
		$success = $validator->validateInput();

		$this->assertFalse($success);
	}

	protected function mockValidatorSuccess()
	{
			$app = m::mock('AppMock');
			$app->shouldReceive('instance')->once()->andReturn($app);

			Illuminate\Support\Facades\Facade::setFacadeApplication($app);
			Illuminate\Support\Facades\Validator::swap($validator = m::mock('validatorMock'));

			$mock_success = m::mock('StdClass');
			$mock_success->shouldReceive(array(
					'fails' => false,
				));

			$validator->shouldReceive('make')
			->with( m::type('Symfony\Component\HttpFoundation\File\UploadedFile') , m::type('array') )
			->once()
			->andReturn($mock_success);
	}

	protected function mockValidatorFail()
	{
			$app = m::mock('AppMock');
			$app->shouldReceive('instance')->once()->andReturn($app);

			Illuminate\Support\Facades\Facade::setFacadeApplication($app);
			Illuminate\Support\Facades\Validator::swap($validator = m::mock('validatorMock'));

			$mock_success = m::mock('StdClass');
			$mock_success->shouldReceive(array(
					'fails' => true,
					'messages' => 'ok',
				));

			$validator->shouldReceive('make')
			->with( m::type('Symfony\Component\HttpFoundation\File\UploadedFile') , m::type('array') )
			->once()
			->andReturn($mock_success);
	}
}