<?php

namespace Tiga\Framework;

use Tiga\Framework\Session\Flash;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Class to handle HTTP Request.
 */
class Request extends SymfonyRequest
{
    /**
     * @var Flash
     */
    protected $flash;

    /**
     * Array holding all previous request input.
     *
     * @var array
     */
    protected $oldInput = false;

    /**
     * Array holding all request input.
     *
     * @var array
     */
    private $input = false;

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        $header = $this->headers->get('CONTENT_TYPE');

        if (strpos($header, 'json') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Populate $input variable with Request.
     */
    private function populateInput()
    {
        // If already populate, return
        if ($this->input) {
            return;
        }

        if ($this->isJson()) {
            $json = new ParameterBag((array) json_decode($this->getContent(), true));
            $this->input = $json->all();
        }
        // GET
        $get = $this->query->all();
        // POST 
        $post = $this->request->all();
        $this->input = array_merge($get, $post);
    }

    /**
     * Get the variable from request using $key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function input($key, $default = false)
    {
        $this->populateInput();

        if (isset($this->input[$key])) {
            return $this->input[$key];
        }

        return $default;
    }

    /**
     * Get all variable from request except for $key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function exclude($key)
    {
        $this->populateInput();

        if (array_key_exists($key, $this->input)) {
            unset($this->input[$key]);
        }

        return $this->input;
    }

    /**
     * Check if the request has $key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        $this->populateInput();

        return array_key_exists($key, $this->input);
    }

    /**
     * Get all variable from request.
     *
     * @return array
     */
    public function all()
    {
        $this->populateInput();

        return $this->input;
    }

    /**
     * Set flash instance.
     *
     * @param Flash $flash
     */
    public function setFlash(Flash $flash)
    {
        $this->flash = $flash;
    }

    /**
     * Flash current request for next request.
     */
    public function flash()
    {
        $this->flash->set('_old_input', $this->all());
    }

    /**
     * Check if old request is flashed.
     */
    public function hasOldInput()
    {
        return  $this->flash->get('_old_input', false);
    }

    /**
     * Populate old input from flash.
     */
    protected function populateOldInput()
    {
        if ($this->flash->has('_old_input')) {
            $this->oldInput = $this->flash->get('_old_input');
        }
    }

    /**
     * Get old request specified by $key.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function oldInput($name = false)
    {
        return $this->flash->get('_old_input', false);
    }

    /**
     * Check request token and throw exception if token is not found.
     */
    public function checkToken()
    {
        // Skip CSRF request by config
        if( \Config::get( 'tiga.skip_csrf_protect',false ) )
            return;

        if ($this->session->get('tiga_csrf_token', false) == false) {
            throw new \Exception('Invalid csrf token');
        }

        $input = $this->input('_tiga_token');

        if ($this->isXmlHttpRequest()) {
            $input = $this->headers->get('X-CSRF-Tiga-Token');
        }

        if ($this->session->get('tiga_csrf_token') != $input) {
            throw new \Exception('Invalid csrf token');
        }
    }
}
