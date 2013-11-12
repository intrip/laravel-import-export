<?php namespace Jacopo\LaravelImportExport\Models;
/**
 * Class that rappresent a line of file: his parameters can be set at runtime
 *
 * @author Jacopo Beschi <beschi.jacopo@gmail.com>
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class RuntimeModel extends Model
{
	// validation rules
	protected $rules;

	protected static $unguarded = true;

	public $timestamps = false;

	protected $connection;

	public function __construc()
	{
		$args = func_get_args();
		$this->connection = Config::get('laravel-import-export::baseconf.connection_name');

		return parent::__construct($args);
	}

	/**
	 * set parameters of the object
	 * @param array $config
	 * @throws InvalidArgumentException
	 */
	public function setConfig(array $configs)
	{
		foreach($configs as $config_key => $config_value)
		{
			if( property_exists(get_class($this), $config_key) )
			{
				$this->$config_key = $config_value;
			}
			else
			{
				throw new \InvalidArgumentException;
			}
		}
	}

	public function forceSetAttribute($key, $value)
	{
		$this->attributes[$key]=$value;
	}

	public function forceSetAttributes(array $attributes)
	{
		foreach($attributes as $key => $value)
		{
			$this->attributes[$key]=$value;
		}
	}

	public function resetAttributes()
	{
		$this->attributes = array();
	}

	public function canSavetoDb()
	{
		return ( isset($this->table) && static::isAssoc($this->attributes) );
	}

	public static function isAssoc($array) {

      return (bool)count(array_filter(array_keys($array), 'is_string'));
	}

	public function getTableName()
	{
		return $this->table;
	}

}