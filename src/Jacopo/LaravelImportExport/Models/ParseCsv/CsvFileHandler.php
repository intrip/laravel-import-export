<?php namespace Jacopo\LaravelImportExport\Models\ParseCsv;
/**
 * Class that handle opening and saving a CsvFile
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileBuilder;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile;
use Jacopo\LaravelImportExport\Models\ParseCsv\DbFileBuilder;
use Jacopo\LaravelImportExport\Models\FileHandlerInterface;
use Jacopo\LaravelImportExport\Models\Exceptions\HeadersNotSetException;
use Jacopo\LaravelImportExport\Models\Exceptions\UnalignedArrayException;
use Jacopo\LaravelImportExport\Models\TemporaryModel;
use Jacopo\LaravelImportExport\Models\Exceptions\NoDataException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Filesystem\FileSystem;
use Jacopo\LaravelImportExport\Models\DatabaseSchemaManager as DBM;

class CsvFileHandler
{
	protected $csv_file;

	/**
	 * Get data from the temporary table
	 *
	 * @return  Mixed $data
	 * @throws  Exception
	 */
	public function getTemporary()
	{
		// disable query logging
		$connection_name = Config::get('LaravelImportExport::baseconf.connection_name');
		DB::connection($connection_name)->disableQueryLog();
		$temporary = TemporaryModel::whereRaw("1")->orderBy("id","DESC")->first();
		if($temporary)
		{
				$this->csv_file =  $temporary->file_object;
				return $this->csv_file;
		}
		return false;
	}

	/**
	 * Put data in the temporary table
	 * 
	 * @return  Boolean $success
	 * @throws  Exception
	 */
	public function saveTemporary($csv_file = null, $temporary_model = null)
	{
		$csv_file =  $csv_file ? $csv_file :  $this->csv_file;
		if( $csv_file )
		{
			$temporary_model = ($temporary_model) ? $temporary_model : new TemporaryModel();
			$temporary_model->fill(array("file_object"=>$csv_file));
			// disable query logging
			$connection_name = Config::get('LaravelImportExport::baseconf.connection_name');
			DB::connection($connection_name)->disableQueryLog();
			return $temporary_model->save();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Update headers of the csv_file 
	 * 
	 * @param CsvFile $csv_line
	 * @param Array $columns $key=column name, $value=column index
	 * @param Sring $table_name name of the table
	 * @throws UnalignedArrayException
	 */
	public function updateHeaders(CsvFile $csv_file, array $columns, $table_name)
	{
		$this->csv_file = $csv_file ? $csv_file : $this->csv_file;

		foreach($this->csv_file as $csv_line)
		{
			$this->updateHeader($csv_line, $columns, $table_name);
		}
	}

	/**
	 * Update headers of the csv_line
	 *
	 * @param CsvLine $csv_line
	 * @param Array $columns $key=column name, $value=column index
	 * @param Sring $table_name name of the table
	 * @return Boolean
	 * @throws UnalignedArrayException
	 */
	protected function updateHeader(CsvLine $csv_line, array $columns, $table_name)
	{
		$model_attributes = $csv_line->getAttributes();
		$new_attributes = array();
		foreach($columns as $key_column => $column)
		{
			if( isset($model_attributes[$key_column]) )
			{
				// append data to new attributes
				$new_attributes = array_merge($new_attributes,array($column => $model_attributes[$key_column]));
				unset($model_attributes[$key_column]);
			}
			else
			{
				throw new UnalignedArrayException;
			}
		}

		// update data
		$csv_line->resetAttributes();
		foreach($new_attributes as $key_attribute => $attribute)
		{
			$csv_line->forceSetAttribute($key_attribute,$attribute);
		}

		$table = array("table" => $table_name);
		$csv_line->setConfig( $table );

		return true;
	}

	/**
	 * @throws HeadersNotSetException
	 * @throws PDOException
	 */
	public function saveToDb(Array $columns_type)
	{
		$csv_file = $this->csv_file;

		// create schema if not exists
		if( ! empty($columns_type))
		{
			$line = $csv_file[0];
			$dbm = new DBM();
			$dbm->createTable($line->getTableName(), $columns_type);
		}

		// disable query logging
		$connection_name = Config::get('LaravelImportExport::baseconf.connection_name');
		DB::connection($connection_name)->disableQueryLog();
		// start transaction
		DB::connection($connection_name)->transaction(function() use($csv_file)
 		{
 			foreach($csv_file as $csv_line)
 			{
 				if($csv_line->canSaveToDb())
 				{
 					$csv_line->save();
 				}
 				else
 				{
 					throw new HeadersNotSetException;
 				}
 			}
 		});

 		return true;
	}

	/**
	 * This method build the CsvFile from a csv file
	 *
	 * @param  $config
	 *         'model' for RunTimeModel
	 *         'file' for ParseCsv
	 *         'builder' for CsvFileBuilder
	 * 
	 * @throws FileNotFoundException
	 * @throws InvalidArgumentException
	 * @throws NoDataException
	 * @throws UnalignedArrayException
	 * @throws DomainException
	 * @return  CsvFile
	 */
	public function openFromCsv(array $config, CsvFileBuilder $builder = null)
	{
		$builder = $builder ? $builder : new CsvFileBuilder();
		$builder->setConfigModel($config["model"]);
		$builder->setConfigFileParse($config["file_parse"]);
		$builder->setConfig($config["builder"]);

		$builder->buildFromCsv();
		$this->csv_file = $builder->getCsvFile();
		if( ! count($this->csv_file))
			throw new NoDataException();

		return $this->getCsvFile();
	}

	/**
	 * Build the csvFile from Db
	 *
	 * @throws  Exception
	 * @throws  PDOException
	 * @return  CsvFile
	 */
	public function openFromDb(array $config, DbFileBuilder $builder = null)
	{
		$builder = $builder ? $builder : new DbFileBuilder();
		$builder->setConfig($config);

		$builder->build();
		$this->csv_file = $builder->getCsvFile();

		return $this->getCsvFile();
	}

	/**
	 * Get the max lenght of a csv_line
	 * @return Integer
	 */
	public function getMaxLength($csv_file = null)
	{
		$csv_file = $csv_file ? $csv_file : $this->csv_file;

		$sizes = array(0);
		if( ! $csv_file )
		{
			throw new NoDataException();
		}

		foreach($csv_file as $line)
		{
			$sizes[]=count($line->getElements() );
		}

		return max($sizes);
	}

	/**
	 * Create the csv string rappresenting the CsvFile
	 * 
	 * @return  String $csv
	 * @throws  NoDataException
	 */
	public function getCsvString($separator, CsvFile $csv_file = null)
	{
		$csv_file = $csv_file ? $csv_file : $this->csv_file;
		$csv = '';
		if( ! isset($csv_file) )
		{
			throw new NoDataException;
		}
		else
		{
			foreach($csv_file as $key => $csv_line)
			{
				if($key == 0)
				{
					$csv.=$csv_line->getCsvHeader($separator);
				}
				$csv.=$csv_line->getCsvString($separator);
			}
		}

		return $csv;
	}

	public function getCsvFile()
	{
		return $this->csv_file;
	}


	public function saveCsvFile($path, $separator = ",")
	{
		$file = new FileSystem();
		return $file->put($path, $this->getCsvString($separator) );
	}

}
