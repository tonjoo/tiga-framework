<?php
namespace Tiga\Framework\Response;
use Flash;

class Response extends \Symfony\Component\HttpFoundation\Response 
{
	/*
	 * Flash input to next request
	 */
	function with($array)
	{
		Flash::set('_old_input',$array);
	}

}