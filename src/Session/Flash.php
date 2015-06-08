<?php

namespace Tiga\Framework\Session;
use Tiga\Framework\Session\Session as Session;

class Flash {

	private $session;

	/*
	 * Hold Flash Data
	 */
	private $data = array();

	function __construct(Session $session)
	{
		$this->session = $session;
	}

	function add($key,$value) {
		return $this->session->getFlashBag()->add($key,$value);
	}

	function set($key,$value) {
		return $this->session->getFlashBag()->set($key,$value);
	}

	function populateFlash($key){

		if(isset($this->data[$key]))
			return;

		if($this->session->getFlashBag()->has($key))
		{
			$flash = $this->session->getFlashBag()->get($key);

			if(sizeof($flash)==1)
				$flash =  $flash[0];

			$this->data[$key] = $flash;
		}

	}

	function get($key,$defaultValue = array()) {
			
		$this->populateFlash($key);

		if($this->has($key))
			return $this->data[$key];

		return $defaultValue;
	}

	function setAll($attributes) {
		return $this->session->getFlashBag()->setAll($attributes);
	}

	function all() {
		return $this->session->getFlashBag()->all();
	}

	function has($key) {
		$this->populateFlash($key);

		return array_key_exists($key,$this->data);
	}

	function peek($key,$defaultValue = array()) {
		return $this->session->getFlashBag()->peek($key,$defaultValue);
	}

	function peekAll() {
		return $this->session->getFlashBag()->peekAll();
	}

	function keys() {
		return $this->session->getFlashBag()->keys();
	}

	function clear() {
		return $this->session->getFlashBag()->clear();
	}

}