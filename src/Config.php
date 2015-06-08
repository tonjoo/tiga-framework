<?php
namespace Tiga\Framework;

class Config
{
	public $data;

	public function __construct($data)
	{
		if(is_array($data))
		{
			$this->data = $data;
			return;
		}

		$this->data = include $data;
	}

	function set($path, $value) 
	{    
	   return array_set($this->data,$path,$value);
	}

	function get($path, $value = null) 
	{
	   return array_get($this->data,$path,$value);
	}

}