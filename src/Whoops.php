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

			if($this->app['request']->isJson())
				$whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
			else
				$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());			

			$whoops->register();
		}
	}
}