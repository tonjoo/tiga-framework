<?php
namespace Tiga\Framework;

class Whoops
{
	protected $app;

	function __construct($app)
	{
		$this->app = $app;
	}

	public function init()
	{
		if(TIGA_DEBUG==true&&!$this->app->isConsole()) 
		{
			// @todo Load Whoops only in debug mode 
			$whoops = new \Whoops\Run();

			// var_dump($this->app['request']);die();

			if($this->app['request']->isXmlHttpRequest())
				$whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
			else
				$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
			
			$this->app['whoops'] = $whoops;			
		}
	}
}