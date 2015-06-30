<?php
namespace Tiga\Framework\ServiceProvider;

/**
 *  Abstract service provider class
 */ 
abstract class AbstractServiceProvider 
{	
    /**
     * @var \Tiga\Framework\App
     */ 
	protected $app;

    /**
     * Construct service provider
     * @param \Tiga\Framework\App $app
     */ 
    public function __construct($app)
    {
    	$this->app = $app;
    }

    /**
     * Register the service provider.
     */
    public function register()
    {

    }

}