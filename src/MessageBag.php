<?php

namespace Tiga\Framework;

/**
 * Convinience class to store an object using array access.
 */
class MessageBag extends Model implements \ArrayAccess
{
    /**
     * @var array
     */
    private $container = array();

    public function __construct()
    {
    }

    /**
     * Set value of the MessageBag object.
     *
     * @param string|int $offset
     * @param mixed      $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Check if array key exist in the Messge Bag.
     *
     * @param string|int $offset
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Unset offset in MessageBag.
     *
     * @param string|int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Get value from array.
     *
     * @param string|int $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!isset($this->container[$offset])) {
            $this->container[$offset] = new self();
        }

        return $this->container[$offset];
    }
}
