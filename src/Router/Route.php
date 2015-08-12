<?php


namespace Tiga\Framework\Router;

class Route
{
    /**
     * Method of the request.
     *
     * @var string
     */
    private $method;

    /**
     * Route.
     *
     * @var string
     */
    private $route;

    /**
     * Route Handler.
     *
     * @var RouteHandler
     */
    private $routeHandler;

    /**
     * @param string       $method
     * @param string       $route
     * @param RouteHandler $routeHandler
     *
     * @return Route
     */
    public function __construct($method, $route, RouteHandler $routeHandler)
    {
        $this->method = $method;

        $this->route = $route;

        $this->routeHandler = $routeHandler;

        return $this;
    }

    /**
     * Gets the Method of the request.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the Method of the request.
     *
     * @param string $method the method
     *
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Gets the Route.
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Gets the Converted Route.
     *
     * @return mixed
     */
    public function getConvertedRoute()
    {
        return $this->convertRouteParam($this->route);
    }

    /**
     * Sets the Route.
     *
     * @param string $route the route
     *
     * @return self
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Gets the routeHandler.
     *
     * @return RouteHandler
     */
    public function getRouteHandler()
    {
        return $this->routeHandler;
    }

    /**
     * Sets the routeHandler.
     *
     * @param RouteHandler $routeHandler the routeHandler
     *
     * @return self
     */
    public function setRouteHandler($routeHandler)
    {
        $this->routeHandler = $routeHandler;

        return $this;
    }

    /**
     * Convert shortcode of regex in route.
     *
     * @param string $route
     *
     * @return type
     */
    public function convertRouteParam($route)
    {
        $patterns = array(
            ':any?' => ':[a-zA-Z0-9\.\-_%=]?+',
            ':num?' => ':[0-9]?+',
            ':all?' => ':.?*',
            ':num' => ':[0-9]+',
            ':any' => ':[a-zA-Z0-9\.\-_%=]+',
            ':all' => ':.*',
        );

        $route = str_replace(array_keys($patterns), array_values($patterns), $route);

        return $route;
    }

    /**
     * Tell the routeHandler to defer the route into desired to later wp_action.
     *
     * @param string $runLevel
     * @param int    $priority
     */
    public function defer($runLevel, $priority = 10)
    {
        $this->routeHandler->defer($runLevel, $priority);
    }

    /**
     * Tell the routeHandler to exit the execution after the controller is executed.
     */
    public function end()
    {
        $this->routeHandler->fastExit();
    }
}
