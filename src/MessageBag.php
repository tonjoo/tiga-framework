<?php
namespace Tiga\Framework;

class MessageBag extends Model implements \ArrayAccess 
{
    private $container = array();

    public function __construct() 
    {
        
    }

    public function offsetSet($offset, $value) 
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) 
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) 
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) 
    {
        if(!isset($this->container[$offset])) 
            $this->container[$offset] = new MessageBag();

        return $this->container[$offset] ;
    }

}