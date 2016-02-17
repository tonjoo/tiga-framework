<?php

namespace Tiga\Framework\Session;

/**
 * Handle flash message.
 */
class Flash
{
    /**
     * @var session
     */
    private $session;

    /**
     * Hold Flash Data.
     *
     * @var array
     */
    private $data = array();

    /**
     * Class constructor.
     *
     * @param Session $session
     *
     * @return Flash
     */
    public function __construct(Session $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Add object to flash bag.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function add($key, $value)
    {
        $this->session->getFlashBag()->add($key, $value);
    }

    /**
     * Set flash object to according to key.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->session->getFlashBag()->set($key, $value);
    }

    /**
     * Copy flash object into local $data. So it can be called repeately.
     *
     * @param string $key
     */
    public function populateFlash($key)
    {
        if (isset($this->data[$key])) {
            return;
        }

        if ($this->session->getFlashBag()->has($key)) {
            $flash = $this->session->getFlashBag()->get($key);
            
            if (sizeof($flash) == 1 && isset($flash[0])) {
                $flash = @$flash[0];
            }

            $this->data[$key] = $flash;
        }
    }

    /**
     * Get object from flash bag.
     *
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = array())
    {
        $this->populateFlash($key);

        if ($this->has($key)) {
            return $this->data[$key];
        }

        return $defaultValue;
    }

    /**
     * Sets all flash messages.
     *
     * @param array $attributes
     */
    public function setAll($attributes)
    {
        $this->session->getFlashBag()->setAll($attributes);
    }

    /**
     * Get all flash bag content.
     *
     * @return array
     */
    public function all()
    {
        return $this->session->getFlashBag()->peekAll();
    }

    /**
     * Check if flash bag has $key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        $this->populateFlash($key);

        return array_key_exists($key, $this->data);
    }

    /**
     * Return all list of defined keys.
     */
    public function keys()
    {
        return $this->session->getFlashBag()->keys();
    }

    /**
     * Clear the flash bag.
     *
     * @return mixed
     */
    public function clear()
    {
        return $this->session->getFlashBag()->clear();
    }
}
