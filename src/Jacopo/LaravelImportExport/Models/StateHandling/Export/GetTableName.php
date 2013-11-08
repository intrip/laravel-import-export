<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Export;
/**
 * State which handle getting and parsing table in the Db
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Jacopo\Bootstrap3Table\BootstrapTable as Table;
use Jacopo\LaravelImportExport\Models\ValidatorFormInputModel;
use Jacopo\LaravelImportExport\Models\DatabaseSchemaManager as DBM;
use Jacopo\LaravelImportExport\Models\InputWrapper as Input;
use Jacopo\LaravelImportExport\Models\Exceptions\NoDataException;

class GetTableName extends GeneralState {

 	protected $next_state_class = "Jacopo\LaravelImportExport\Models\StateHandling\Export\GetTableConfig";

 	protected $rules = array(
 			"table_name" => "required",
 			"max_rows" => "required|integer"
 		);

	/**
	 * Return the form that needs to be processed
	 */
	protected function printFormNew(){
		$str = parent::printFormNew();
		$str .=
				"<div class=\"row\">\n".
				"<div class=\"col-md-12\">\n".
				"<h3>Step1: get table data</h3>\n".
				"<span class=\"glyphicon glyphicon-open pull-right icon-medium\" style=\"top:-38px;\"></span>\n".
				Form::open(array( 'url' => $this->getProcessUrl(), 'role' => 'form'))."\n".
				$this->createSelectTables().
				"<div class=\"form-group\">".
				Form::label('separator','Type of separator: ').
				Form::select('separator' , array( ','=>'comma ,' , ';'=>'semicolon ;' ) , ',', array("class" => "form-control") ).
				"</div>".
				"<div class=\"form-group\">\n".
				Form::label('max_rows','Max number of lines to show in preview: ')."\n".
				Form::select('max_rows' , array('10'=>'10', '50'=>'50', '100'=>'100', '150'=>'150') , '10', array("class"=> "form-control") )."\n".
				"</div>\n".
				Form::submit('Open' , array('class'=>'btn btn-success'))."\n".
				Form::close().
				"</div>\n". // form-group
				"</div>\n". // col
				"</div>\n". // row
				"<hr/>\n";

		return $str;
	}
	
	/**
	 * Return the form that has already been processed
	 */
	protected function printFormOld(){
		$reset_url = $this->getResetUrl();

		$str=<<<STR
<div class="row bottom-10">
<div class="col-md-12">
	<a class="btn btn-danger pull-right" href="$reset_url"><span class="glyphicon glyphicon-trash"></span> Reset Export</a>
</div>
</div>
<hr/>
<div class="row">
<div class="col-md-12">
	<b>Open table: completed succesfully!</b><span class="glyphicon glyphicon-ok pull-right icon-medium" style="top: -6px;"></span>
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
	public function processForm(array $input = null, $validator = null)
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

		if ( $validator->validateInput() )
		{
			try
			{
				$table_preview = $this->getDataTable();
			}
			catch(BadMethodCallException $e)
			{
				$this->appendError("BadMethod" , "Invalid input data: cannot show the table." );
				return $this->getIsExecuted();
			}
			catch(NoDataException $e)
			{
				$this->appendError("NoData" , "The table is empty." );
				return $this->getIsExecuted();
			}
			$exchange_data = array(
					"preview" => $table_preview,
					"table_name" => $this->form_input["table_name"],
					"form_input" => $this->form_input,
					"column_names" => $this->getTableHeader(),
					"separator" => $this->form_input["separator"]
				);
			$this->is_executed = true;
			// save data for next state
			Session::put($this->exchange_key, $exchange_data);
		}
		else
		{
			$this->appendError("fileEmpty" , "No data found in the file" );
		}

		return $this->getIsExecuted();
	}

	public function fillFormInput(Input $input = null)
	{
		$input = $input ? $input : new Input();
		$this->form_input['table_name'] = $input->get('table_name');
		$this->form_input['max_rows'] = $input->get('max_rows');
		$this->form_input['separator'] = $input->get('separator');
	}

	protected function createSelectTables()
	{
		$table_list = $this->getTableValuesSelect();

		if($table_list)
		{
			$select = "<div class=\"form-group\">\n".
						Form::label('table_name','Table name to open:')."\n".
						Form::select('table_name' , $table_list , reset($table_list) , array("class"=> "form-control") )."\n".
						"</div>\n";
		}
		else
		{
			$this->appendError("NoTables" , "No tables found in the db" );
			$select = '';
		}

		return $select;
	}

	/**
	 * Return value with table names for a select
	 * @return Array
	 */
	protected function getTableValuesSelect($dbm = null)
	{
		$dbm = $dbm ? $dbm : new DBM;
		$table_raw = $dbm->getTableList();

		return $table_raw ? array_combine(array_values($table_raw), array_values($table_raw) ) : false;
	}

	/**
	 * Return the table for the preview
	 * @return String $table
	 */
	protected function getDataTable()
	{
		$data = $this->getTableData();
		$header = $this->getTableHeader();

		$table = new Table();
		// set the configuration
		$table->setConfig(array(
			"table-hover"=>true, 
			"table-condensed"=>false, 
			"table-responsive" => true,
			"table-striped" => true,
			 ));
		// set header
		$table->setHeader( $header );
		// set data
		foreach($data as $val)
		{
			$table->addRows( get_object_vars($val) );
		}

		return $table->getHtml();
	}

	/**
	 * Get the data from the db for the preview
	 * 
	 * @throws  BadMethodCallException
	 * @throws  NoDataException
	 * @return  Array $data
	 */
	protected function getTableData()
	{
		$data = array();
		$form_input = $this->getFormInput();

		if( $form_input['table_name'] && $form_input['max_rows'] )
		{
			$connection_name = Config::get('LaravelImportExport::baseconf.connection_name');
			$temp_data = DB::connection($connection_name)
				->table($this->form_input['table_name'])
				->select("*")
				->take($this->form_input['max_rows']);
			if( $temp_data->exists() )
			{
				$data = $temp_data->get();
			}
			else
			{
				throw new NoDataException;
			}
		}
		else
		{
			throw new \BadMethodCallException;
		}

		return $data;
	}

	/**
	 * Get the header for the preview
	 * 
	 * @throws  BadMethodCallException
	 * @return  Array $columns
	 */
	protected function getTableHeader($dbm = null)
	{
		$dbm = $dbm ? $dbm : new DBM;
		$columns = array();
		$form_input = $this->getFormInput();

		if( $form_input['table_name'] )
		{
			$dbal_columns = $dbm->getTableColumns($this->form_input['table_name']);
			foreach($dbal_columns as $column)
			{
				$array_columns = $column->toArray();
				$columns[] = $array_columns["name"];
			}
		}
		else
		{
			throw new \BadMethodCallException;
		}

		return $columns;
	}

}
