<?php
namespace Tiga\Framework\Response;

class Response extends \Symfony\Component\HttpFoundation\Response 
{
	/*
	 * isJson
	 */
	function isJson()
	{
		return $this->headers->get('Content-Type')=='application/json';
	}
	
	function getWpStatusCodeHeader()
	{
		return sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText);
	}
}