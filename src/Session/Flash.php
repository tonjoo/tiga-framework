<?php

namespace Tiga\Framework\Session;
use Tiga\Framework\Session\Session as Session;

class Flash {

	private $session;

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

	function get($key,$defaultValue = array()) {
		
		$flash = $this->session->getFlashBag()->get($key,$defaultValue);

		if(sizeof($flash)==1)
			return $flash[0];

		return $flash;
	}

	function setAll($attributes) {
		return $this->session->getFlashBag()->setAll($attributes);
	}

	function all() {
		return $this->session->getFlashBag()->all();
	}

	function has($key) {
		return $this->session->getFlashBag()->has($key);
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