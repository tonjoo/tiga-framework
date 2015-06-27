<?php
namespace Tiga\Framework;

class Config
{
	public $data;

	/**
	 * Construct config from array or file
	 * @param string|array $data 
	 */
	public function __construct($data)
	{
		if(is_array($data))
		{
			$this->data = $data;
			return;
		}

		$this->data = include $data;
	}

	/**
	 * Set config value
	 * @param string $path 
	 * @param mixed $value 
	 * @return array
	 */
	function set($path, $value) 
	{    
	   return array_set($this->data,$path,$value);
	}

	/**
	 * Set config value
	 * @param string $path 
	 * @param mixed $value 
	 * @return array
	 */
	function get($path, $value = null) 
	{
	   return array_get($this->data,$path,$value);
	}

}