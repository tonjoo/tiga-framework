<?php
namespace Tiga\Framework\Response;

class Header 
{

	protected $statusCode;

	protected $response;

	public function __construct()
	{
		$this->hook();
	}

	public function setResponse($response)
	{
		$this->response = $response;
	}	

	public function hook()
	{
		add_filter('status_header',array($this,'sendResponse'),100);
	}

	public function sendResponse()
	{
		
		if($this->response!=false)
		{
			$this->response->sendHeaders();
			return $this->response->getWpStatusCodeHeader();						
		}
	}
}