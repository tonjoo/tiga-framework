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
		return $session->getFlashBag()->add($key,$value);
	}

	function set($key,$value) {
		return $session->getFlashBag()->set($key,$value);
	}

	function get($key,$defaultValue = array()) {
		
		$flash = $session->getFlashBag()->get($key,$defaultValue);

		if(sizeof($flash)==1)
			return $flash[0];

		return $flash;
	}

	function setAll($attributes) {
		return $session->getFlashBag()->setAll($attributes);
	}

	function all() {
		return $session->getFlashBag()->all();
	}

	function has($key) {
		return $session->getFlashBag()->has($key);
	}

	function peek($key,$defaultValue = array()) {
		return $session->getFlashBag()->peek($key,$defaultValue);
	}

	function peekAll() {
		return $session->getFlashBag()->peekAll();
	}

	function keys() {
		return $session->getFlashBag()->keys();
	}

	function clear() {
		return $session->getFlashBag()->clear();
	}

}