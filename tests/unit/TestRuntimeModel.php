<?php

use Jacopo\LaravelImportExport\Models\RuntimeModel;

class TestRuntimeModel extends PHPUnit_Framework_TestCase {

	protected $model;

	public function setUp()
	{
		$this->model = new RuntimeModel();
	}

	public function testValidConfig()
	{
		$config = array("timestamps" => true);

		$this->model->setConfig($config);

		$this->assertObjectHasAttribute("timestamps", $this->model);

	}

	public function testForceSetAttributesSuccess()
	{
		$atts = array(
					"first" => 1,
					"second" => 2,
					 );
		$this->model->forceSetAttributes($atts);

		$this->assertEquals($atts["first"],$this->model->first);
		$this->assertEquals($atts["second"],$this->model->second);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionInvalidConfig()
	{

		$config = array("invalid" => "invalid");

		$this->model->setConfig($config);
	}

	public function testCanSaveToDbSuccess()
	{
		$config = array("table"=>"table");

		$this->model->setConfig($config);
		// set a string attribute
		$this->model->key = 0;
		$can_save =  $this->model->canSaveToDb();

		$this->assertTrue($can_save);
	}

	public function testCanSaveToDbFailsTable()
	{
		$config = array();

		$this->model->setConfig($config);
		// set a string attribute
		$this->model->key = 0;
		$can_save =  $this->model->canSaveToDb();

		$this->assertFalse($can_save);
	}

	public function testCanSaveToDbFailsAttributes()
	{
		$config = array("table"=>"table");

		$this->model->setConfig($config);
		$can_save =  $this->model->canSaveToDb();

		$this->assertFalse($can_save);
	}
}