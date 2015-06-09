<?php
namespace Tiga\Framework\Response;
use Flash;

class Response extends \Symfony\Component\HttpFoundation\Response 
{
	/*
	 * isJson
	 */
	function isJson()
	{
		return $this->headers->get('Content-Type')=='application/json';
	}

}