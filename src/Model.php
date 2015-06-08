<?php
namespace Tiga\Framework;

class Model {

	private $data = array();

	public function __construct()
	{

	}

	public function __set($name, $value) 
	{
        $this->data[$name] = $value;
    }

    public function __get($name) 
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

}