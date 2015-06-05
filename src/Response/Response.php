<?php
namespace Tiga\Framework\Response;

class Response extends Symfony\Component\HttpFoundation\Response 
{

	private $flash; 

	function __construct($flash)
	{
		$this->flash = $flash;
	}

	/*
	 * Flash input to next request
	 */
	function with($array)
	{
		$this->flash->set('_old_input',$array);
	}

}