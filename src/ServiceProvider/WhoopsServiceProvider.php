<?php
namespace Tiga\Framework\ServiceProvider;

class WhoopsServiceProvider extends AbstractServiceProvider
{
	public function register()
	{		
		if(TIGA_DEBUG==true&&!$this->app->isConsole()) 
		{
			// @todo Load Whoops only in debug mode 
			$whoops = new \Whoops\Run();			
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
			$this->app['whoops'] = $whoops;			
		}
	}
}