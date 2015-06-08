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
     * Das handler
     */
    private $handler;

    public function __construct($handler)
    {
    	$this->handler = $handler;
    }

    public function getHandler()
    {
    	return $this->handler;
    }

    public function defer($runLevel,$priority=10)
    {
        $this->deferred = true; 
        $this->runLevel = $runLevel;
        $this->priority = $priority;
    }

    public function isDeferred()
    {
        return $this->deferred;
    }

    public function getRunLevel()
    {
        return $this->runLevel;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function fastExit()
    {
        $this->fastExit = true;
    }

    public function isFastExit()
    {
        return $this->fastExit;
    }

}