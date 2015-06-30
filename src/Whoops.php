<?php
namespace Tiga\Framework;

/**
 * Implement error handling  with Whoops
 */
class Whoops
{
	/**
	 * @var \Tiga\Framework\App
	 */
	protected $app;
	
	/**
	 * Construct Tiga Whoops implementation
	 * @param \Tiga\Framework\App $app 
	 * @return Whoops
	 */
	function __construct($app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Init Whoops based on App conditional rule
	 */
	public function init()
	{
		if(TIGA_DEBUG==true&&!$this->app->isConsole()) 
		{ 
			$whoops = new \Whoops\Run();

			if($this->app['request']->isJson())
				$whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
			else
				$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());			

			$whoops->register();
		}
	}
}