<?php

namespace Tiga\Framework;

use Tonjoo\Almari\Container as Container;
use Tiga\Framework\Console\Console as Console;

/**
 * Tiga application container.
 */
class App extends Container
{
    /**
     * @var \Tiga\Framework\Console\Console
     */
    private $console = false;

    /**
     * @var array
     */
    private $config = array();

    /**
     * Construct App.
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load registered service provider in the main plugin and child plugin.
     */
    private function loadServiceProvider()
    {
        \Tiga\Framework\Facade\Facade::setFacadeContainer($this);

        // Available Provider
        $providers = $this['config']->get('provider');

        $providers = array_unique($providers);

        foreach ($providers as $provider) {
            $instance = new $provider($this);
            $instance->register();
        }

        // Aliases
        $aliases = $this['config']->get('alias');

        $aliases = array_unique($aliases);

        $aliasMapper = \Tonjoo\Almari\AliasMapper::getInstance();

        $aliasMapper->classAlias($aliases);
    }

    /**
     * Load app/config.php in the main plugin and child plugin.
     */
    private function loadConfig()
    {
        // Load All Config
        $this->config = apply_filters('tiga_config', array());

        $this['config'] = new Config($this->config);
    }

    /**
     * Init the router, not applicable for console command.
     */
    public function routerInit()
    {
        $this->loadServiceProvider();
        // Load All Config
        do_action('tiga_routes');

        $this['router']->init();
    }

    /**
     * Register facade for all registered service provider.
     */
    public function registerServiceProvider()
    {
        $aliasMapper = Tonjoo\Almari\AliasMapper::getInstance();

        //Register Facade class alias
        $aliasMapper->facadeClassAlias($alias);
    }

    /**
     * Tell if the Tiga running in console mode or not.
     */
    public function isConsole()
    {
        return (boolean) $this->console;
    }

    /**
     * Get Tiga console.
     */
    public function getConsole()
    {
        $this->console = true;
        $this->loadServiceProvider();

        return new Console();
    }
}
