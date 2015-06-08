<?php
namespace Tiga\Framework\ServiceProvider;

class FormServiceProvider extends AbstractServiceProvider
{
	public function register()
	{	

		$this->app->shareDeferred('flashFormOldProvider',function(){
			return new \Tiga\Framework\Html\FlashFormOldInput($this->app['request']);;
		}); 

		$this->app->shareDeferred('html', function(){
			return new \Tiga\Framework\Html\HtmlBuilder();
		});

		$this->app->shareDeferred('form',function(){
			return new \Tiga\Framework\Html\FormBuilder($this->app['html'],$this->app['flashFormOldProvider'],$this->app['flash']);
		});  

	}
}