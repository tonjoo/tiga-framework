<?php
namespace Tiga\Framework\ServiceProvider;

abstract class AbstractServiceProvider 
{	
	protected $app;

    public function __construct($app)
    {
    	$this->app = $app;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }

}