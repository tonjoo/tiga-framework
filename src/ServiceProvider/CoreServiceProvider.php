<?php
namespace Tiga\Framework\ServiceProvider;

/**
 *  Service provider to load mandatory class
 */ 
class CoreServiceProvider extends AbstractServiceProvider
{

	public function register()
	{		
		$this->app['app'] = $this->app;

		$this->app->shareDeferred('routes',function(){
			return new \Tiga\Framework\Router\Routes();
		});

		$this->app->shareDeferred('request',function(){
			return  \Tiga\Framework\Request::createFromGlobals();			
		});

		$this->app->shareDeferred('router',function(){
			return new \Tiga\Framework\Router\Router($this->app['routes'],$this->app['request'],$this->app,$this->app['view']);

		});

		$this->app->shareDeferred('view',function(){
			return new \Tiga\Framework\View\View($this->app['template']);
		});

		$this->app->shareDeferred('template',function(){

			$config['path'] = $this->app['config']->get('path.view');
			$config['storage'] = $this->app['config']->get('path.storage');

			return new \Tiga\Framework\Template\Template($config);

		});

		$this->app->shareDeferred('responseFactory',function(){
			return new \Tiga\Framework\Response\ResponseFactory();
		});

		$this->app->bind('db',function(){

			$connection = new \Tiga\Framework\Database\WPDBConnection();

			$queryCompiler = new \Tiga\Framework\Database\QueryCompiler($connection);

			return new \Tiga\Framework\Database\QueryBuilder($queryCompiler,$connection);

		});

		$this->app->shareDeferred('validator',function(){

			return new \Tiga\Framework\Validator();

		});

		$this->app->shareDeferred('pagination',function(){

			return new \Tiga\Framework\Pagination();

		});
	}
} 