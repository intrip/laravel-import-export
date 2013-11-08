<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Export;
/**
 * State which handle getting final configuration to export data
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Session;
use Jacopo\LaravelImportExport\Models\Exceptions\NoDataException;
use Jacopo\LaravelImportExport\Models\ValidatorFormInputModel;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileHandler;
use Jacopo\LaravelImportExport\Models\InputWrapper as Input;

class GetTableConfig extends GeneralState {

 	protected $next_state_class = "Jacopo\LaravelImportExport\Models\StateHandling\Export\ExportCompleteState";
 	protected $csv_path = "download.csv";
 	protected $rules = array();

	/**
	 * Return the form that needs to be processed
	 */
	protected function printFormNew(){
		$prev_data = Session::get($this->exchange_key);
		$str = parent::printFormNew();
		$str.= "<h3>Step2: fill save settings</h3>".
				$prev_data["preview"];
		// config form
		$str.="<h4>Fill the form below:</h4>";
		$str.= Form::open(array( 'url' => $this->getProcessUrl(), "role" => "form", "class" => "form-horizontal") );
		$str.= "<h5>Write the name of each column to export (\"__empty\"=don't export)</h5>";
		$columns = $prev_data["column_names"];
		foreach( $columns as $key => $column)
		{
			$str.= "<div class=\"form-group\">".
					 Form::label("{$column}", "{$column}: ", array("class"=>"control-label col-lg-2")).
					 "<div class=\"col-lg-10\">".
					 Form::text("{$column}", $column ,array("class"=>"form-control") ).
					 "</div>".
					 "</div>";
		}
		$str.= "<div class=\"form-group\">".
				 "<div class=\"col-lg-offset-2 col-lg-10\">".
				 Form::submit('Export' , array('class'=>'btn btn-success') ).
				 "</div>".
				 "</div>";
		$str.= Form::close();

		return $str;
	}
	
	/**
	 * Return the form that has already been processed
	 */
	protected function printFormOld(){
		$str = <<<STR
<div class="row">
<div class="col-md-12">
	<b>Export table: completed succesfully!</b><span class="glyphicon glyphicon-ok pull-right icon-medium" style="top: -6px;"></span>
<hr/>
</div>
</div>
STR;
		return $str;
	}

	/**
	 * Process the form
	 *
	 * @return  Boolean $is_executed if the state is executed successfully
	 */
	public function processForm(array $input = null, $validator = null, CsvFileHandler $handler = null)
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
		$handler = $handler ? $handler : new CsvFileHandler();

		if ( $validator->validateInput() )
		{
			$builder_config = array(
					"columns" => $this->form_input["attributes"],
					"table" => $this->form_input["table_name"],
				);
			try
			{
				$handler->openFromDb($builder_config);
			}
			catch(\PDOException $e)
			{
				$this->appendError("DbException" , $e->getMessage() );
				return $this->getIsExecuted();
			}
			catch(\Exception $e)
			{
				$this->appendError("DbException" , $e->getMessage() );
				return $this->getIsExecuted();
			}
			try
			{
				$exchange_data = Session::get($this->exchange_key);
				$separator = $exchange_data["separator"];
				$success = $handler->saveCsvFile($this->csv_path,$separator);
				if($success)
				{
					Session::put($this->exchange_key,$this->csv_path);
					$this->is_executed = true;
				}
				else
				{
					$this->appendError("Permission" , "Cannot save temporary data: please set write permission on public folder");
					return $this->getIsExecuted();
				}
			}
			catch(NoDataException $e)
			{
				$this->appendError("NoData" , "No data to save");
				return $this->getIsExecuted();
			}
		}
		else
		{
			$this->appendError("fileEmpty" , "No data found in the file" );
		}

		return $this->getIsExecuted();
	}

	public function fillFormInput($input = null)
	{
		$input = $input ? $input : new Input();

		$prev_data = Session::get($this->exchange_key);

		$this->form_input["table_name"] = $prev_data["table_name"];
		$columns = $prev_data["column_names"];
		$this->form_input["attributes"] = array();
		foreach( $columns as $key => $column)
		{
			$this->form_input["attributes"][$column] = $input->get($column);
		}
	}
}
