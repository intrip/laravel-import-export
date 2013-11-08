<?php namespace Jacopo\LaravelImportExport\Models\StateHandling\Import;
/**
 * State which handle getting and parsing the Csv File
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Exception;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Jacopo\LaravelImportExport\Models\ValidatorFormInputModel;
use Jacopo\LaravelImportExport\Models\ParseCsv\CsvFileHandler;
use Jacopo\LaravelImportExport\Models\Exceptions\FileNotFoundException;
use Jacopo\LaravelImportExport\Models\Exceptions\InvalidArgumentException;
use Jacopo\LaravelImportExport\Models\Exceptions\NoDataException;
use Jacopo\LaravelImportExport\Models\Exceptions\UnalignedArrayException;
use Jacopo\LaravelImportExport\Models\Exceptions\DomainException;

class GetCsvState extends GeneralState {

 	protected $next_state_class = "Jacopo\LaravelImportExport\Models\StateHandling\Import\GetTableState";

 	protected $rules = array(
 			"file_csv" => "required",
 		);

	/**
	 * Return the form that needs to be processed
	 */
	protected function printFormNew(){
		$str = parent::printFormNew();
		$str .=
				"<div class=\"row\">".
				"<div class=\"col-md-12\">".
				"<h3>Step1: Upload file</h3>".
				// "<span class=\"glyphicon glyphicon-upload pull-right icon-medium\"></span>".
				Form::open(array( 'url' => $this->getProcessUrl(), 'files' => true , 'role' => 'form')).
				"<div class=\"form-group\">".
				Form::label('file_csv', 'Select file to upload (CSV only for now) *').
				Form::file('file_csv').
				"</div>".
				"<div class=\"form-group\">".
				Form::label('separator','Type of separator: ').
				Form::select('separator' , array( ','=>'comma ,' , ';'=>'semicolon ;' ) , ',', array("class" => "form-control") ).
				"</div>".
				"<div class=\"form-group\">".
				Form::label('max_lines','Max number of lines to show in preview: ').
				Form::select('max_lines' , array('10'=>'10', '50'=>'50', '100'=>'100', '150'=>'150') , '10', array("class"=> "form-control") ).
				"</div>".
				"<div class=\"form-group checkbox\">".
				"<label>".
				Form::checkbox('headers','1',false).
				"Check if first line contains headers".
				"</label>".
				"</div>".
				"<div class=\"form-group checkbox\">".
				"<label>".
				Form::checkbox('create_table','1',false).
				"Check if want to create table schema (Warning: will overwrite table data)".
				"</label>".
				"</div>".
				"<div class=\"form-group\">".
				Form::submit('Load' , array('class'=>'btn btn-success')).
				Form::close().
				"</div>". // form-group
				"</div>". // col
				"</div>". // row
				"<hr/>";

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
	<a class="btn btn-danger pull-right" href="$reset_url"><span class="glyphicon glyphicon-trash"></span> Reset Import</a>
</div>
</div>
<hr/>
<div class="row">
<div class="col-md-12">
	<b>Upload file: completed succesfully!</b><span class="glyphicon glyphicon-ok pull-right icon-medium" style="top: -6px;"></span>
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

		if ( $validator->validateInput() && $this->checkInputCsv() )
		{
			// process data
			$config = $this->getConfigFromInput();
			$success = true;
			// big try catch to set errors
			try
			{
				$csv_file = $csv_handler->openFromCsv($config);
			}
			catch(FileNotFoundException $e)
			{
				$this->appendError("NotFound", "File not found." );
				$success = false;
			}
			catch(InvalidArgumentException $e)
			{
				$this->appendError("InvalidArgument", "Invalid argument." );
				$success = false;
			}
			catch(DomainException $e)
			{
				$this->appendError("Domain", "Invalid temporary data." );
				$success = false;
			}
			catch(UnalignedArrayException $e)
			{
				$this->appendError("UnalignedArray", "Unaligned data." );
				$success = false;
			}
			catch(NoDataException $e)
			{
				$this->appendError("NoData", "No data found in the file." );
				$success = false;
			}

			// save temporary
			if($success)
			{
				$saved = false;

				try
				{
					$saved = $csv_handler->saveTemporary();
				}
				catch( Exception $e)
				{
					$this->appendError("SaveTemporary" , "Cannot save temporary data: check database access configuration." );
				}

				if($saved)
				{
					$this->is_executed = true;
					// add if want to create table to session
					$exchange_data = array(
							"max_lines" => $this->form_input['max_lines'],
							"create_table" => $this->form_input['create_table']
						);
					// save data for next state
					Session::put($this->exchange_key,$exchange_data);
				}
			}
		}

		return $this->getIsExecuted();
	}

	public function fillFormInput()
	{
		if(Input::hasFile('file_csv'))
		{
			$this->form_input['file_csv'] = Input::file('file_csv');
		}else
		{
			$this->form_input['file_csv'] = '';
		}
		$this->form_input['separator'] = Input::get('separator');
		$this->form_input['headers'] = Input::get('headers');
		$this->form_input['max_lines'] = Input::get('max_lines');
		$this->form_input['create_table'] = Input::get('create_table');
	}

	protected function checkInputCsv()
	{
		$valid = false;

		$input = $this->form_input['file_csv'];
		if( ! empty($input) )
		{
			$name = $this->form_input['file_csv']->getClientOriginalName();
			if(strstr($name,"."))
			{
				$name = explode(".",$name);
				$extension = end($name);
				if(strcasecmp($extension,"csv") == 0)
					$valid = true;
			}
		}

		if( ! $valid )
		{
			$this->appendError("Invalid_extension", "The input file must be .csv" );
		}

		return $valid;
	}

	// get the config from form input
	protected function getConfigFromInput()
	{
		$config_file_parse['separator'] = $this->form_input['separator'];
		$config_file_parse['file_path'] = $this->form_input['file_csv']->getRealPath();
		$config["file_parse"] = $config_file_parse;

		$config_builder['first_line_headers'] = ( $this->form_input['headers'] != '' );
		$config["builder"] = $config_builder;

		$config_model = array();
		$config["model"] = $config_model;

		return $config;
	}

}
