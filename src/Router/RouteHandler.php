<?php
namespace Tiga\Framework\Router;

class RouteHandler 
{

    /**
     * Deferred execution
     */
    protected $deferred = false;

    /**
     * Exit execution
     */
    protected $fastExit = false;

    /**
     * Run Level
     */
    private $runLevel;
    
    /**
     * Run priority
     */
    private $priority;

    /**
     * The handler
     */
    private $handler;

    /**
     * @param RouteHandler $handler 
     * @return this
     */
    public function __construct($handler)
    {
    	$this->handler = $handler;

        return $this;
    }

    /**
     * Return routeHandler
     * @return RouteHandler
     */
    public function getHandler()
    {
    	return $this->handler;
    }

    /**
     * Defer the route into desired to later wp_action
     * @param string $runLevel 
     * @param int $priority 
     */
    public function defer($runLevel,$priority=10)
    {
        $this->deferred = true; 
        $this->runLevel = $runLevel;
        $this->priority = $priority;
    }

    /**
     * Check if the routeHanlder deffered or not
     * @return boolean
     */
    public function isDeferred()
    {
        return $this->deferred;
    }

    /**
     * Return desired wp_action run level
     * @return string
     */
    public function getRunLevel()
    {
        return $this->runLevel;
    }

    /**
      * Return the wp_action priority
      * @return int
      */ 
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set the routeHandler for terminating the request after execution
     */

    public function fastExit()
    {
        $this->fastExit = true;
    }

    /**
     * Check if the routeHandler is fastExit state
     * @return boolean
     */

    public function isFastExit()
    {
        return $this->fastExit;
    }

}