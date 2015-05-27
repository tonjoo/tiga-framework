<?php
namespace Tiga\Framework;
use Tonjoo\Almari\Container as Container;
use Tiga\Framework\Facade\RouterFacade as Router;
use Tiga\Framework\Console\Console as Console;
use Tiga\Framework\Config as Config;

class App extends Container
{
	private $console = false;
	private $config = array();

	function __construct()
	{
		$this->loadConfig();
		$this->loadProvider();
	}

	private function loadProvider()
	{
		\Tiga\Framework\Facade\Facade::setFacadeContainer($this);

		// Available Provider
		$providers = $this['config']->get('provider');

		foreach ($providers as $provider) {
			$instance = new $provider($this);
			$instance->register();
		}

		// Aliases
		$aliases = $this['config']->get('alias');

		$aliasMapper = \Tonjoo\Almari\AliasMapper::getInstance();

		$aliasMapper->classAlias($aliases);

	}

	private function loadConfig()
	{
		// Load All Config
		$this->config = apply_filters('tiga_config',array());

		$this['config'] = new Config($this->config);
		
	}

	function routerInit() 
	{
		// Load All Config
	  	do_action('tiga_routes');

		Router::init();
	}

	function registerServiceProvider()
	{

		$aliasMapper = Tonjoo\Almari\AliasMapper::getInstance();

		//Register Facade class alias
		$aliasMapper->facadeClassAlias($alias);
	}

	function isConsole()
	{
		return (boolean) $this->console;
	}

	function getConsole() 
	{
		return new Console();
	}

}