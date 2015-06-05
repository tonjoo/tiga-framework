<?php
namespace Tiga\Framework\ServiceProvider;

class SessionServiceProvider extends AbstractServiceProvider
{
	public function register()
	{	
		// Do not load session in console
		if($this->app->isConsole())
			return;


		// Initializing Session
		$storage = new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(array(), new \Tiga\Framework\Session\WPSessionHandler());
		$session = new \Tiga\Framework\Session\Session($storage);
		$session->start();

		$this->app['session'] = $session;

		$flash = new \Tiga\Framework\Session\Flash($session);
		$this->app['flash'] = $flash;

	}
}