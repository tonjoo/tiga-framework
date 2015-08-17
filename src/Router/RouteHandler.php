<?php

namespace Tiga\Framework\Router;

class RouteHandler
{
    /**
     * Deferred execution.
     *
     * @var bool
     */
    protected $deferred = false;

    /**
     * Exit execution.
     *
     * @var bool
     */
    protected $fastExit = false;

    /**
     * Run Level.
     *
     * @var string
     */
    private $runLevel;

    /**
     * Run priority.
     *
     * @var int
     */
    private $priority;

    /**
     * The handler.
     *
     * @var RouteHandler
     */
    private $handler;

    /**
     * @param RouteHandler $handler
     *
     * @return this
     */
    public function __construct($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Return routeHandler.
     *
     * @return RouteHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Defer the route into desired to later wp_action.
     *
     * @param string $runLevel
     * @param int    $priority
     */
    public function defer($runLevel, $priority = 10)
    {
        $this->deferred = true;
        $this->runLevel = $runLevel;
        $this->priority = $priority;
    }

    /**
     * Check if the routeHanlder deffered or not.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->deferred;
    }

    /**
     * Return desired wp_action run level.
     *
     * @return string
     */
    public function getRunLevel()
    {
        return $this->runLevel;
    }

    /**
     * Return the wp_action priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set the routeHandler for terminating the request after execution.
     */
    public function fastExit()
    {
        $this->fastExit = true;
    }

    /**
     * Check if the routeHandler is fastExit state.
     *
     * @return bool
     */
    public function isFastExit()
    {
        return $this->fastExit;
    }
}
