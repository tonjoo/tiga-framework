<?php
namespace Tiga\Framework;

/**
 *  Base class for Tiga Model
 */ 
abstract class Model {

    /**
     * @var array
     */ 
	private $data = array();

	public function __construct()
	{

	}

    /**
     * Magic setter to set properties
     * @param string $name 
     * @param mixed $value 
     */
	public function __set($name, $value) 
	{
        $this->data[$name] = $value;
    }

    /**
     * Magic getter
     * @param string $name
     * @return mixed 
     */ 
    public function __get($name) 
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * Check if properties of a model exist
     * @param string
     * @return mixed 
     */ 
    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

}