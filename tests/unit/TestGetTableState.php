<?php

use Jacopo\LaravelImportExport\Models\StateHandling\Import\GetTableState;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile;
use Jacopo\Bootstrap3Table\BootstrapTable as Table;
use Mockery as m;

class TestGetTableState extends PHPUnit_Framework_TestCase {

	protected $state;

	public function setUp()
	{
		$this->state =  m::mock('Jacopo\LaravelImportExport\Models\StateHandling\Import\GetTableState')->makePartial();
	}

	public function tearDown()
	{
		m::close();
	}

	public function testFillFormInput()
	{
		$input_mock = m::mock('Jacopo\LaravelImportExport\Models\InputWrapper');
		$input_mock->shouldReceive(//array("get"=>2,"get"=>2,"get"=>2,"get"=>2));
   		'get',
   		'get',
   		'get',
   		'get',
   		'get',
   		'get',
   		'get'
   	)->andReturn(
   		'table_name',
   		'2',
   		'1',
   		'first',
   		'string',
   		'second',
   		'string'
   	);
		$this->state->fillFormInput($input_mock);
		$form_input = $this->state->getFormInput();

		$this->assertEquals('table_name', $form_input["table_name"]);
		$expected_vals = array(
				0 => 'first',
				1 => 'second'
			);
		$this->assertEquals($expected_vals, $form_input["columns"]);
		$expected_types = array(
				'first' => 'string',
				'second' => 'string',
			);
		$this->assertEquals($expected_types, $form_input["types"]);		
	}

	public function testProcessFormSuccess()
	{
		$mock_validator = m::mock('Jacopo\LaravelImportExport\Models\ValidatorFormInputModel');
		$mock_validator->shouldReceive('validateInput')
		->once()
		->andReturn(true);

		$form_input = array(
					'table_name' => 'table',
					'columns' => array(
							"first" => 0,
							"second" => 1
						),
					'types' => array(),
				);

		$mock_csv_handler = m::mock('Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileHandler');
		$mock_csv_handler->shouldReceive(array(
			'getTemporary' => new CsvFile(),
			'updateHeaders' => true,
			'saveToDb' => true
		));

		$executed = $this->state->processForm($form_input,$mock_validator,$mock_csv_handler);
		$this->assertSame(true,$executed);
	}

}