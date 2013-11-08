<?php

use Jacopo\LaravelImportExport\Models\ParseCsv\File;

class TestFile extends PHPUnit_Framework_TestCase {

	protected $file_wrapper;

	public function setUp()
	{
		$this->file_wrapper = new File();
	}

	/**
	*@expectedException Jacopo\LaravelImportExport\Models\Exceptions\FileNotFoundException
	*/
	public function testOpenFileThrowsFileNotFoundException()
	{
		$path = "notfound.csv";

		$this->file_wrapper->openFile($path);
	}
}