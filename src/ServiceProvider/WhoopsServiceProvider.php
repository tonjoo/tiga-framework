<?php
namespace Tiga\Framework\ServiceProvider;

class WhoopsServiceProvider extends AbstractServiceProvider
{
	public function register()
	{	

		$this->app['whoops']  = new \Tiga\Framework\Whoops($this->app);

	}
}