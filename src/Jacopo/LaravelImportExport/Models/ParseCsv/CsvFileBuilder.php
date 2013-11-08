<?php namespace Jacopo\LaravelImportExport\Models\ParseCsv;
/**
 * Class builder of CsvFile
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */
class CsvFileBuilder
{

	protected $config_file_parse;
	protected $config_model;
	protected $csv_file;
	protected $csv_parser;
	/**
	 * 'first_line_headers' if first line of the file contains data headers
	 * @var Array
	 */
	protected $config;

	/**
	 * 
	 * @param  $config_model configuration for the RuntimeModel
	 * @param  $config_file_parse configuration for the csv file parser
	 * @param  $config configuration for the builder
	 * @param  $csv_file instance of CsvFile
	 * @param  $csv_parser istance of ParseCsv
	 */
	public function __construct(array $config_model = array(), array $config_file_parse = array(), $config = array(), CsvFile $csv_file = null, ParseCsv $csv_parser = null)
	{
		$this->config_model = $config_model;
		$this->config_file_parse = $config_file_parse;
		$this->config = $config;
		$this->csv_file = $csv_file ? $csv_file : new CsvFile;
		$this->csv_parser = $csv_parser ? $csv_parser : new ParseCsv;
	}

	public function setConfigModel($config)
	{
		$this->config_model = $config;
	}
	public function setConfigFileParse($config)
	{
		$this->config_file_parse = $config;
	}
	public function setConfig($config)
	{
		$this->config = $config;
	}
	/**
	 * This method build the CsvFile from a csv file
	 *
	 * @throws FileNotFoundException
	 * @throws InvalidArgumentException
	 * @throws UnalignedArrayException
	 * @throws DomainException
	 */
	public function buildFromCsv(CsvLine $csv_line_base = null)
	{
		$this->csv_line_base = $csv_line_base ? $csv_line_base : new CsvLine;
		$this->csv_parser->setConfig($this->config_file_parse);
		$this->csv_line_base->setConfig($this->config_model);

		$this->updateCsvHeader();

		// Csv loop
		while ( ($csv_line_array = $this->csv_parser->parseCsvFile()) != false )
		{
			if( count($csv_line_array) && (! ( (count($csv_line_array) == 1) && is_null($csv_line_array[0]) ) ) )
			{
				// create csv_line
				$csv_line = clone $this->csv_line_base;
				$csv_line->forceSetAttributes($csv_line_array);
				$this->appendLine($csv_line);
			}
		}
	}

	public function getCsvFile()
	{
		return $this->csv_file;
	}

	protected function appendLine(CsvLine $line)
	{
		$this->csv_file->append($line);
	}

	/**
	 * Update the csv header with first row of the file
	 * if "first_line_headers" is enabled
	 */
	protected function updateCsvHeader()
	{
		if( $this->config['first_line_headers'] )
		{
			$csv_line_array = $this->csv_parser->parseCsvFile();
			$this->csv_file->setCsvHeader($csv_line_array);
		}
	}

}
