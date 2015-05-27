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
	   $loc = &$this->data;
	   foreach(explode('.', $path) as $step)
	   {
	     $loc = &$loc[$step];
	   }

	   return $loc = $value;
	}

	function get($path, $value = null) 
	{
	   $loc = &$this->data;
	   
	   foreach(explode('.', $path) as $step)
	   {
	   	 if(!isset($loc[$step]))
	   	 	return $value;

	     $loc = &$loc[$step];

	   }
	   
	   return $loc;
	}

}