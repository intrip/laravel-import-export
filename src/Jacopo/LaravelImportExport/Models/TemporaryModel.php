<?php namespace Jacopo\LaravelImportExport\Models;
/**
 * Temporary model used to save data
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class TemporaryModel extends Model {

	protected $table;

	protected $fillable = array('file_object','id');

	public $timestamps = false;

	protected $connection;

	// test model saving and getting
	public function __construct()
	{
		$this->table = Config::get('LaravelImportExport::baseconf.table_prefix');
		$this->connection = Config::get('LaravelImportExport::baseconf.connection_name');

		$params = func_get_args();

		return parent::__construct($params);
	}

	// encode object for saving
    public function setFileObjectAttribute($value)
	{
		$this->attributes['file_object'] = base64_encode( serialize($value) );
	}

	// decode object for saving
    public function getFileObjectAttribute()
	{
		return unserialize( base64_decode( $this->attributes['file_object'] ) );
	}

}
