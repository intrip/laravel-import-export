<?php namespace Jacopo\LaravelImportExport\Models\ParseCsv;
/**
 * Class that rappresent a csv line
 * Holds all the information about the line to save in the Db
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Jacopo\LaravelImportExport\Models\RuntimeModel;
use Illuminate\Support\Facades\Config;

class CsvLine extends RuntimeModel
{
	public function __construct()
	{
		$this->connection = Config::get('LaravelImportExport::baseconf.connection_name');
		return parent::__construct( func_get_args() );
	}

	public function getElements()
	{
		return $this->toArray();
	}

	public function getCsvString($separator)
	{
		$attributes = $this->toArray();
		return $this->arrayToCsv($attributes, $separator);
	}

	public function getCsvHeader($separator)
	{
		$attributes_keys = array_keys( $this->toArray() );
		return $this->arrayToCsv($attributes_keys, $separator);
	}

	protected function arrayToCsv($attributes, $separator)
	{
		$str = '';
		$attribute_size = count($attributes);
		$current_item = 1;
		foreach($attributes as $key => $attribute)
		{
			$str.="{$attribute}";
			if( $current_item++ < $attribute_size)
			{
				$str.= $separator;
			}
		}
		$str.= "\n";

		return $str;
	}
}