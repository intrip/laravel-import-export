<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Import;
/**
 * State which handle saving file into the db
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Session;
use Jacopo\LaravelImportExport\Models\InputWrapper as Input;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileHandler;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFile;
use Jacopo\LaravelImportExport\Models\ValidatorFormInputModel;
use Jacopo\LaravelImportExport\Models\Exceptions\UnalignedArrayException;
use Jacopo\LaravelImportExport\Models\Exceptions\NoDataException;
use Jacopo\LaravelImportExport\Models\Exceptions\HeadersNotSetException;
use Jacopo\Bootstrap3Table\BootstrapTable as Table;
use Exception;

class GetTableState extends GeneralState {

 	protected $next_state_class = "Jacopo\LaravelImportExport\Models\StateHandling\Import\ImportCompleteState";

	protected $rules = array(
			"table_name" => "required"
		);

	/**
	 * Return the form that needs to be processed
	 */
	protected function printFormNew(){

		$str = parent::printFormNew();
		
		$csv_handler = new CsvFileHandler();
		$csv_file = $csv_handler->getTemporary();

		$str.=  "<div class=\"row\">".
					"<div class=\"col-md-12\">".
					"<h3>Step2: Configure import data</h3>".
					"<h5>Sample data from csv file:</h5>";
		try
		{
			$str.= $this->getCsvTableStr($csv_handler, $csv_file);
		}
		catch(NoDataException $e)
		{
		}

		$str.= "<h4>Fill the form below:</h4>";
		$exchange_data = Session::get($this->exchange_key);
		$setSchema = $exchange_data["create_table"];
		$str.= $this->getConfigForm($csv_handler, $csv_file, $setSchema);

		$str.=	"</div>". // col-md-12
				"</div>"; // row

		return $str;
	}
	
	/**
	 * Return the form that has already been processed
	 */
	protected function printFormOld(){

		$str=<<<STR
<div class="row">
<div class="col-md-12">
	<b>Save file: completed succesfully!</b><span class="glyphicon glyphicon-ok pull-right icon-medium" style="top: -6px;"></span>
<hr/>
</div>
</div>

STR;
		return $str;
	}

	protected function getConfigForm(CsvFileHandler $csv_handler, CsvFile $csv_file, $setSchema = false)
	{
		$str = Form::open(array( 'url' => $this->getProcessUrl(), "role" => "form", "class" => "form-horizontal") );
		
		// table name
		$str.= "<div class=\"form-group\">".
				 Form::label("table_name", "Table name to save: *", array("class"=>"control-label col-lg-2")).
				 "<div class=\"col-lg-10\">".
				 Form::text("table_name","table" ,array("class"=>"form-control") ).
				 "</div>".
				 "</div>";

		// column names
		$str.= "<h5>Write the name of each column to save into the DB, the index number are the same as the column number in the sample data(\"__empty\"=don't save)</h5>";
		$num_cols = $csv_handler->getMaxLength();
		$header = $csv_file->getCsvHeader();
		foreach( range(0,$num_cols-1) as $key)
		{
			$default_value = isset($header[$key]) ? $header[$key] : "__empty";
			$str.= "<div class=\"form-group\">".
					 Form::label("column_{$key}", "Column".($key+1).": ", array("class"=>"control-label col-lg-2") ).
					 "<div class=\"col-lg-10\">".
					 Form::text("column_{$key}", $default_value ,array("class"=>"form-control bottom-10") ).
					 "</div>";
			if($setSchema)
			{
				$str.= $this->getSelectSchema("column_type_{$key}");
				$str.= Form::hidden('create_schema','1');
			}
			$str.="</div>";
		}
		$str.= Form::hidden("num_cols",$num_cols);
		$str.= "<div class=\"form-group\">".
				 "<div class=\"col-lg-offset-2 col-lg-10\">".
				 Form::submit('Import' , array('class'=>'btn btn-success') ).
				 "</div>".
				 "</div>";

		$str.= Form::close();
		
		return $str;

	}

	/**
	 * Return a select for the type of data to create
	 * 
	 * @param  $name select name and id
	 * @return String $select
	 */
	protected function getSelectSchema($name)
	{
		$data_types = array(
				"string" => "string",
				"integer" => "integer",
				"increments" => "increments",
				"bigIncrements" => "bigIncrements",
				"bigInteger" => "bigInteger",
				"smallInteger" => "smallInteger",
				"float" => "float",
				"double" => "double",
				"decimal" => "decimal",
				"boolean" => "boolean",
				"date" => "date",
				"dateTime" => "dateTime",
				"time" => "time",
				"blob" => "binary",
			);

		$select =Form::label($name,'Type: ', array("class"=>"control-label col-lg-2") ).
				"<div class=\"col-lg-10\">".
				Form::select($name , $data_types, '', array("class" => "form-control") ).
				"</div>";

		return $select;
	}

	/**
	 * Return the table rappresenting the csv data
	 *
	 * @throws  NoDataException
	 * @return  String $table
	 */
	protected function getCsvTableStr(CsvFileHandler $csv_handler, CsvFile $csv_file)
	{
		if(! $csv_file)
		{
			throw new NoDataException();
		}

		$table = new Table();
		// set the configuration
		$table->setConfig(array(
				"table-hover"=>true, 
				"table-condensed"=>false, 
				"table-responsive" => true,
				"table-striped" => true,
			 ));
		
		// set header
		if( $csv_header = $csv_file->getCsvHeader() )
		{
			$table->setHeader( $csv_header );
		}
		
		// set content
		$exchange_data = Session::get($this->exchange_key);
		$max_num_lines = $exchange_data["max_lines"];
		foreach( $csv_file as $csv_line_key => $csv_line)
		{
			if($csv_line_key >= $max_num_lines)
				break;

			// add data table row
			$table->addRows( $csv_line->getElements() );
		}

		

		return $table->getHtml();
	}

	/**
	 * Process the form and return the next state
	 * @return  Boolean $is_executed
	 */
	public function processForm(array $input = null, $validator = null, $csv_handler = null)
	{
		if($input)
		{
			$this->form_input = $input;
		}
		else
		{
			$this->fillFormInput();
		}

		$validator = ($validator) ? $validator : new ValidatorFormInputModel($this);
		$csv_handler = ($csv_handler) ? $csv_handler : new CsvFileHandler();

		if ( $validator->validateInput() && $this->checkColumns() )
		{
			// process form
			$csv_file = $csv_handler->getTemporary();
			if($csv_file)
			{
				try
				{
					$csv_handler->updateHeaders($csv_file, $this->form_input["columns"], $this->form_input["table_name"]);
				}
				catch(UnalignedArrayException $e)
				{
					$this->appendError("No_temporary", "Invalid Column names." );
					return $this->getIsExecuted();
				}

				try
				{
					$csv_handler->saveToDb($this->form_input['types']);
					$this->is_executed = true;
				}
				catch( HeadersNotSetException $e)
				{
					$this->appendError("Save_err", "Cannot save data: check if dmbs support transaction.");
				}
				catch( PDOException $e)
				{
					$this->appendError("Save_err", $e->getMessage() );
				}
				catch( Exception $e)
				{
					$this->appendError("Save_err", $e->getMessage() );
				}
			}
			else
			{
				$this->appendError("No_temporary","No data available to save");
			}
		}
		return $this->getIsExecuted();
	}

	public function fillFormInput(Input $input = null)
	{
		$input = $input ? $input : new Input();
		$this->form_input['table_name'] = $input->get('table_name');
		$num_cols = $input->get('num_cols');
		if($num_cols)
		{
			$array_cols = array();
			$array_type = array();
			// if need to create schema
			$this->form_input['create_schema'] = $input->get('create_schema');
			foreach( range(0,$num_cols-1) as $key)
			{
				$column_name = $input->get("column_{$key}");
				if( $column_name != '__empty')
				{
					$array_cols = array_merge($array_cols , array( $key => $column_name) );
					$column_type = $input->get("column_type_{$key}");
					$array_type = array_merge($array_type , array( $column_name => $column_type) );
				}
			}
			$this->form_input['types'] = $array_type;
			$this->form_input['columns'] = $array_cols;
		}
	}

	/**
	 * Check the column field if atleast one exists
	 * @return Boolean $success
	 */
	protected function checkColumns()
	{
		$success = false;

		if ( ! empty($this->form_input['columns']) )
		{
			$success = true;
		}
		else
		{
			$this->appendError("No_columns","No column name setted: you need to fill atleast one \"column_\" field");
		}

		return $success;
	}

}
