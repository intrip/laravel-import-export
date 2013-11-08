<?php namespace Jacopo\LaravelImportExport\Models\ParseCsv;
/**
 * Simple File wrapper
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Jacopo\LaravelImportExport\Models\Exceptions\FileNotFoundException;

class File
{

	/**
 	 * @throws FileNotFoundException
 	 * @return  Handle file handler
	 */
	public function openFile($file_path)
	{
		if( $file_path && file_exists($file_path) )
			{
				return fopen($file_path, "r");
			}
			else
			{
				throw new FileNotFoundException;
			}
	}

	public function closeFile($file)
	{
		return fclose($file);
	}

	public function readCsv($file,$max_length,$separator)
	{
		return fgetcsv($file,$max_length,$separator);
	}

}
