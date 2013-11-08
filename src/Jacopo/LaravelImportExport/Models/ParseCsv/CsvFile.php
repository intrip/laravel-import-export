<?php namespace Jacopo\LaravelImportExport\Models\ParseCsv;
/**
 * Class that rappresent a CVS File
 * The other information are inside CsvLine Class
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

class CsvFile extends \ArrayIterator
{
	protected $csv_headers = array();

	public function getCsvHeader()
	{
		return $this->csv_headers;
	}

	public function setCsvHeader($value)
	{
		$this->csv_headers = $value;
	}

}
