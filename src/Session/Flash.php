<?php

namespace Tiga\Framework\Session;
use Tiga\Framework\Facade\SessionFacade as Session;

class Flash {

	function add($key,$value) {
		return Session::getFlashBag()->add($key,$value);
	}

	function set($key,$value) {
		return Session::getFlashBag()->set($key,$value);
	}

	function get($key,$defaultValue = array()) {
		
		$flash = Session::getFlashBag()->get($key,$defaultValue);

		if(sizeof($flash)==1)
			return $flash[0];

		return $flash;
	}

	function setAll($attributes) {
		return Session::getFlashBag()->setAll($attributes);
	}

	function all() {
		return Session::getFlashBag()->all();
	}

	function has($key) {
		return Session::getFlashBag()->has($key);
	}

	function peek($key,$defaultValue = array()) {
		return Session::getFlashBag()->peek($key,$defaultValue);
	}

	function peekAll() {
		return Session::getFlashBag()->peekAll();
	}

	function keys() {
		return Session::getFlashBag()->keys();
	}

	function clear() {
		return Session::getFlashBag()->clear();
	}

}