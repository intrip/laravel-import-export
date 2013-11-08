<?php namespace Jacopo\LaravelImportExport\Models\ParseCsv;
/**
 * Class to parse CSV FILE
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

Class ParseCsv
{
	/**
	 * Attributes:
	 * 	file_path
	 *  	separator
	 */
	protected $config;
	protected $csvFile;
	protected $file;
	protected $file_wrapper;

	public function __construct(array $config=null, File $file_wrapper = null)
	{
		if($config)
		{
			$this->config=$config;
		}

		$this->file_wrapper = $file_wrapper ?: new File;

	}


	public function parseCsvFile()
	{
		if( ! isset($this->file) )
		{
			$this->openFile();
		}

		$separator = (isset($this->config["separator"])) ? $this->config["separator"] : ",";
		$csv_line_array = $this->file_wrapper->readCsv($this->file,0,$separator);

		if( (! $csv_line_array) && (! is_null($csv_line_array)) )
		{
			$this->closeFile();
		}

		return $csv_line_array;
	}

	private function closeFile()
	{
		if( isset($this->file))
			{
				$this->file_wrapper->closeFile($this->file);
				unset($this->file);
			}
	}

	/**
	 * Open csv file
	 * @return Handle file
	 * @throws FileNotFoundException
	 */
	private function openFile()
	{
		$file_path = isset( $this->config["file_path"] ) ? $this->config["file_path"] : null;

		$this->file = $this->file_wrapper->openFile($file_path);
	}

	public function setConfig(array $value)
	{
		$this->config=$value;
	}
}