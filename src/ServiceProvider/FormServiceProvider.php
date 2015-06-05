<?php
namespace Tiga\Framework\ServiceProvider;

class FormServiceProvider extends AbstractServiceProvider
{
	public function register()
	{	
		// Do not load session in console
		if($this->app->isConsole())
			return;

		$this->app['flashFormOldProvider'] = new \Tiga\Framework\HTML\FlashFormOldInput($this->app['flash']);

		$this->app['form'] = new \Tiga\Framework\HTML\FormBuilder($this->app['flashFormOldProvider']);

	}
}