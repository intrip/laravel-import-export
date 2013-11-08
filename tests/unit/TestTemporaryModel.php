<?php

use Jacopo\LaravelImportExport\Models\TemporaryModel;
use Mockery as m;

class TestTemporaryModel extends PHPUnit_Framework_TestCase {

	public function setup()
	{
		$this->mockConfig();
	}

	public function tearDown()
	{
		m::close();
	}

	public function testCanIstantiateModel()
	{
		$model = new TemporaryModel();
	}

	protected function mockConfig()
	{
		$app = m::mock('AppMock');
		$app->shouldReceive('instance')->once()->andReturn($app);

		Illuminate\Support\Facades\Facade::setFacadeApplication($app);
		Illuminate\Support\Facades\Config::swap($config = m::mock('ConfigMock'));

		$config->shouldReceive('get','get')
			->andReturn('_import_export_temporary_table','import');
	}
}